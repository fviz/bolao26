<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import GamesList from '@/components/games/GamesList.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TopScorerPlayerPicker from '@/components/predictions/TopScorerPlayerPicker.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import type { CountrySearchTerms } from '@/composables/usePlayerSearch';
import { useGameSchedule } from '@/composables/useGameSchedule';
import {
    destroy as destroyChampionPrediction,
    upsert as upsertChampionPrediction,
} from '@/routes/champion-prediction';
import { index as predictionsIndex } from '@/routes/predictions';
import {
    destroy as destroyTopScorerPrediction,
    upsert as upsertTopScorerPrediction,
} from '@/routes/top-scorer-prediction';
import type {
    ChampionPrediction,
    ChampionTeam,
    GameListItem,
    Paginated,
    TopScorerPrediction,
    WorldCupPlayer,
} from '@/types/game';

type Props = {
    predictedGames: Paginated<GameListItem>;
    missingGames: Paginated<GameListItem>;
    championPrediction: ChampionPrediction | null;
    championPredictionsOpen: boolean;
    championPredictionsDeadline: string;
    championTeams: ChampionTeam[];
    topScorerPrediction: TopScorerPrediction | null;
    topScorerPredictionsOpen: boolean;
    topScorerPredictionsDeadline: string;
    players: WorldCupPlayer[];
    playerCountrySearchTerms: CountrySearchTerms;
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

const showChampionForm = ref(false);
const showTopScorerForm = ref(false);
const selectedPlayerId = ref<string | null>(
    props.topScorerPrediction?.playerId ?? null,
);

const championTeamName = computed(() => {
    if (!props.championPrediction) {
        return null;
    }

    return (
        props.championTeams.find(
            (team) => team.fifaTeamId === props.championPrediction?.fifaTeamId,
        )?.name ?? props.championPrediction.fifaTeamId
    );
});

const selectedTopScorerPlayer = computed(() => {
    if (!props.topScorerPrediction) {
        return null;
    }

    return (
        props.players.find(
            (player) => player.id === props.topScorerPrediction?.playerId,
        ) ?? null
    );
});

const topScorerDisplayName = computed(() => {
    const player = selectedTopScorerPlayer.value;

    if (!player) {
        return props.topScorerPrediction?.playerId ?? null;
    }

    const club = player.club ? ` · ${player.club}` : '';

    return `${player.name} — ${player.position}${club} · ${player.country}`;
});

const showChampionPredictionForm = computed(
    () =>
        props.championPredictionsOpen &&
        props.championTeams.length > 0 &&
        (!props.championPrediction || showChampionForm.value),
);

const showTopScorerPredictionForm = computed(
    () =>
        props.topScorerPredictionsOpen &&
        props.players.length > 0 &&
        (!props.topScorerPrediction || showTopScorerForm.value),
);

const canManageChampionPrediction = computed(
    () =>
        props.championPrediction !== null && props.championPredictionsOpen,
);

const canManageTopScorerPrediction = computed(
    () =>
        props.topScorerPrediction !== null && props.topScorerPredictionsOpen,
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Minhas previsões',
                href: predictionsIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Minhas previsões" />

    <div class="flex flex-col gap-8 p-4 md:p-6">
        <Heading
            variant="small"
            title="Minhas previsões"
            description="Acompanhe suas previsões e os jogos que ainda faltam apostar"
        />

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div
                class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div class="flex items-start justify-between gap-2">
                    <p class="text-muted-foreground text-sm">Campeão</p>
                    <DropdownMenu v-if="canManageChampionPrediction">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8 shrink-0"
                                aria-label="Opções do palpite de campeão"
                            >
                                <MoreHorizontal class="size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem
                                @select="
                                    (event) => {
                                        event.preventDefault();
                                        showChampionForm = true;
                                    }
                                "
                            >
                                Editar palpite
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                variant="destructive"
                                as-child
                            >
                                <Form
                                    v-bind="destroyChampionPrediction.form()"
                                    :options="{ preserveScroll: true }"
                                    class="w-full"
                                    @success="showChampionForm = false"
                                >
                                    <button
                                        type="submit"
                                        class="w-full cursor-default text-left"
                                    >
                                        Remover palpite
                                    </button>
                                </Form>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <p v-if="championPrediction" class="font-medium">
                    {{ championTeamName }}
                    <span
                        v-if="championPrediction.points !== null"
                        class="text-green-700 dark:text-green-400"
                    >
                        (+{{ championPrediction.points }} pts)
                    </span>
                </p>
                <p v-else-if="championPredictionsOpen" class="text-sm">
                    Escolha abaixo até
                    {{
                        formatScheduledAt(championPredictionsDeadline)
                            .combined
                    }}
                </p>
                <p v-else class="text-muted-foreground text-sm">
                    Prazo encerrado
                </p>
            </div>

            <div
                class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div class="flex items-start justify-between gap-2">
                    <p class="text-muted-foreground text-sm">Artilheiro</p>
                    <DropdownMenu v-if="canManageTopScorerPrediction">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8 shrink-0"
                                aria-label="Opções do palpite de artilheiro"
                            >
                                <MoreHorizontal class="size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem
                                @select="
                                    (event) => {
                                        event.preventDefault();
                                        showTopScorerForm = true;
                                        selectedPlayerId =
                                            topScorerPrediction?.playerId ??
                                            null;
                                    }
                                "
                            >
                                Editar palpite
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                variant="destructive"
                                as-child
                            >
                                <Form
                                    v-bind="destroyTopScorerPrediction.form()"
                                    :options="{ preserveScroll: true }"
                                    class="w-full"
                                    @success="showTopScorerForm = false"
                                >
                                    <button
                                        type="submit"
                                        class="w-full cursor-default text-left"
                                    >
                                        Remover palpite
                                    </button>
                                </Form>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <p v-if="topScorerPrediction" class="font-medium">
                    {{ topScorerDisplayName }}
                    <span
                        v-if="topScorerPrediction.points !== null"
                        class="text-green-700 dark:text-green-400"
                    >
                        (+{{ topScorerPrediction.points }} pts)
                    </span>
                </p>
                <p v-else-if="topScorerPredictionsOpen" class="text-sm">
                    Escolha abaixo até
                    {{
                        formatScheduledAt(topScorerPredictionsDeadline)
                            .combined
                    }}
                </p>
                <p v-else class="text-muted-foreground text-sm">
                    Prazo encerrado
                </p>
            </div>
        </div>

        <section
            v-if="showChampionPredictionForm"
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <h2 class="mb-3 text-lg font-semibold">Palpite de campeão</h2>
            <Form
                v-bind="upsertChampionPrediction.form()"
                class="flex flex-wrap items-end gap-4"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing }"
                @success="showChampionForm = false"
            >
                <div class="grid min-w-48 flex-1 gap-2">
                    <Label for="champion_team">Seleção campeã</Label>
                    <select
                        id="champion_team"
                        name="fifa_team_id"
                        class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        required
                        :default-value="championPrediction?.fifaTeamId"
                    >
                        <option value="" disabled>
                            Selecione uma seleção
                        </option>
                        <option
                            v-for="team in championTeams"
                            :key="team.fifaTeamId"
                            :value="team.fifaTeamId"
                        >
                            {{ team.name }}
                        </option>
                    </select>
                    <InputError :message="errors.fifa_team_id" />
                </div>
                <Button type="submit" :disabled="processing">
                    {{
                        championPrediction
                            ? 'Atualizar campeão'
                            : 'Salvar campeão'
                    }}
                </Button>
            </Form>
        </section>

        <section
            v-if="showTopScorerPredictionForm"
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <h2 class="mb-3 text-lg font-semibold">Palpite de artilheiro</h2>
            <Form
                v-bind="upsertTopScorerPrediction.form()"
                class="flex sm:flex-row flex-col flex-wrap sm:items-end gap-4"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing }"
                @success="showTopScorerForm = false"
            >
                <TopScorerPlayerPicker
                    v-model="selectedPlayerId"
                    :players="players"
                    :country-search-terms="playerCountrySearchTerms"
                    :error="errors.player_id"
                />
                <Button
                    type="submit"
                    :disabled="processing || !selectedPlayerId"
                >
                    {{
                        topScorerPrediction
                            ? 'Atualizar artilheiro'
                            : 'Salvar artilheiro'
                    }}
                </Button>
            </Form>
        </section>

        <section class="flex flex-col gap-4">
            <Heading
                variant="small"
                title="Com previsão"
                description="Jogos em que você já registrou um placar"
            />

            <GamesList
                :games="predictedGames"
                empty-message="Você ainda não fez nenhuma previsão."
                action-label="Ver jogo"
                show-prediction-column
                pagination-aria-label="Paginação de jogos com previsão"
            />
        </section>

        <section class="flex flex-col gap-4">
            <Heading
                variant="small"
                title="Sem previsão"
                description="Jogos abertos para aposta — encerram 1 minuto antes do apito inicial"
            />

            <GamesList
                :games="missingGames"
                empty-message="Nenhum jogo aberto para aposta no momento."
                action-label="Fazer previsão"
                pagination-aria-label="Paginação de jogos sem previsão"
            />
        </section>
    </div>
</template>
