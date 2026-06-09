<script setup lang="ts">
import { Form, Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed, ref, watchEffect } from 'vue';
import GameCommentsSection from '@/components/games/GameCommentsSection.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { dashboard } from '@/routes';
import { upsert as upsertPrediction } from '@/routes/games/prediction';
import type { GameComment, GameListItem } from '@/types/game';

type Props = {
    game: GameListItem;
    comments: GameComment[];
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();
const schedule = formatScheduledAt(props.game.scheduledAt);

const homeScoreInput = ref(props.game.userPrediction?.homeScore ?? 0);
const awayScoreInput = ref(props.game.userPrediction?.awayScore ?? 0);
const showPredictionForm = ref(!props.game.userPrediction);

const isDrawPrediction = computed(
    () => homeScoreInput.value === awayScoreInput.value,
);

const shouldShowPredictionForm = computed(
    () =>
        props.game.isBettingOpen &&
        (!props.game.userPrediction || showPredictionForm.value),
);

const canShowPredictionOptions = computed(
    () =>
        props.game.isBettingOpen &&
        !!props.game.userPrediction &&
        !showPredictionForm.value,
);

const showPenaltyPicker = computed(
    () =>
        props.game.isKnockout &&
        props.game.isBettingOpen &&
        isDrawPrediction.value,
);

const penaltyWinnerLabel = (side: string | null): string => {
    if (side === 'home') {
        return props.game.home.displayName;
    }

    if (side === 'away') {
        return props.game.away.displayName;
    }

    return '—';
};

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Painel do Bolão',
                href: dashboard(),
            },
            {
                title: props.game.matchTitle,
            },
        ],
    });
});

const venueLabel = (): string => {
    if (props.game.stadiumName && props.game.cityName) {
        return `${props.game.stadiumName}, ${props.game.cityName}`;
    }

    return props.game.stadiumName ?? props.game.cityName ?? '—';
};

function showPredictionEditor(event: Event): void {
    event.preventDefault();

    homeScoreInput.value =
        props.game.userPrediction?.homeScore ?? homeScoreInput.value;
    awayScoreInput.value =
        props.game.userPrediction?.awayScore ?? awayScoreInput.value;
    showPredictionForm.value = true;
}
</script>

<template>
    <Head :title="game.matchTitle" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading
            variant="small"
            :title="game.matchTitle"
            :description="
                [game.stageName, game.groupName].filter(Boolean).join(' · ') ||
                undefined
            "
        />

        <section
            v-if="game.isFinal && game.result"
            class="rounded-xl border border-green-200 bg-green-50 p-4 md:p-6 dark:border-green-900 dark:bg-green-950/30"
        >
            <Heading
                variant="small"
                title="Resultado final"
                :description="
                    game.result.penaltyWinner
                        ? `Empate no tempo regulamentar — vencedor nos pênaltis: ${penaltyWinnerLabel(game.result.penaltyWinner)}`
                        : undefined
                "
            />
            <p class="mt-2 text-2xl font-bold">
                {{ game.result.homeScore }} × {{ game.result.awayScore }}
            </p>
        </section>

        <section class="flex flex-col gap-4 rounded-xl border p-4 md:p-6">
            <GameMatchDisplay
                :home="game.home"
                :away="game.away"
                layout="stacked"
            />
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Data e hora</dt>
                    <dd class="font-medium">{{ schedule.combined }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Estádio</dt>
                    <dd class="font-medium">{{ venueLabel() }}</dd>
                </div>
                <div v-if="game.matchNumber">
                    <dt class="text-muted-foreground">Jogo</dt>
                    <dd class="font-medium">#{{ game.matchNumber }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border p-4 md:p-6">
            <div class="flex items-start justify-between gap-2">
                <Heading
                    variant="small"
                    title="Seu palpite"
                    :description="
                        game.isBettingOpen
                            ? 'Palpites aceitos até 1 minuto antes do apito inicial'
                            : 'Palpites encerrados para este jogo'
                    "
                />
                <DropdownMenu v-if="canShowPredictionOptions">
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-8 shrink-0"
                            aria-label="Opções do palpite"
                        >
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @select="showPredictionEditor">
                            Editar palpite
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div v-if="!game.isBettingOpen" class="mt-4 space-y-3">
                <p v-if="game.userPrediction" class="text-lg font-semibold">
                    {{ game.userPrediction.homeScore }} ×
                    {{ game.userPrediction.awayScore }}
                    <span
                        v-if="game.userPrediction.penaltyWinner"
                        class="text-base font-normal text-muted-foreground"
                    >
                        (pênaltis:
                        {{
                            penaltyWinnerLabel(
                                game.userPrediction.penaltyWinner,
                            )
                        }})
                    </span>
                </p>
                <p v-else class="text-sm text-muted-foreground">
                    Você não registrou palpite para este jogo.
                </p>
                <p
                    v-if="
                        game.userPrediction?.points !== null &&
                        game.userPrediction?.points !== undefined
                    "
                    class="text-lg font-semibold text-green-700 dark:text-green-400"
                >
                    {{ game.userPrediction.points }} pontos neste jogo
                </p>
                <p class="text-sm text-muted-foreground">
                    O prazo encerrou em
                    {{ formatScheduledAt(game.bettingClosesAt).combined }}.
                </p>
            </div>

            <div
                v-else-if="game.userPrediction && !showPredictionForm"
                class="mt-4 space-y-3"
            >
                <p class="text-lg font-semibold">
                    {{ game.userPrediction.homeScore }} ×
                    {{ game.userPrediction.awayScore }}
                    <span
                        v-if="game.userPrediction.penaltyWinner"
                        class="text-base font-normal text-muted-foreground"
                    >
                        (pênaltis:
                        {{
                            penaltyWinnerLabel(
                                game.userPrediction.penaltyWinner,
                            )
                        }})
                    </span>
                </p>
            </div>

            <Form
                v-else-if="shouldShowPredictionForm"
                v-bind="upsertPrediction.form(game.id)"
                class="mt-4 space-y-4"
                v-slot="{ errors, processing }"
                @success="showPredictionForm = false"
            >
                <div class="flex flex-wrap items-end gap-4">
                    <div class="grid gap-2">
                        <Label :for="`home_score_${game.id}`">{{
                            game.home.displayName
                        }}</Label>
                        <Input
                            :id="`home_score_${game.id}`"
                            name="home_score"
                            type="number"
                            min="0"
                            max="20"
                            class="w-24"
                            required
                            v-model.number="homeScoreInput"
                        />
                        <InputError :message="errors.home_score" />
                    </div>
                    <span class="pb-2 text-lg font-medium text-muted-foreground"
                        >×</span
                    >
                    <div class="grid gap-2">
                        <Label :for="`away_score_${game.id}`">{{
                            game.away.displayName
                        }}</Label>
                        <Input
                            :id="`away_score_${game.id}`"
                            name="away_score"
                            type="number"
                            min="0"
                            max="20"
                            class="w-24"
                            required
                            v-model.number="awayScoreInput"
                        />
                        <InputError :message="errors.away_score" />
                    </div>
                </div>

                <div v-if="showPenaltyPicker" class="space-y-3">
                    <Label>Vencedor nos pênaltis</Label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="penalty_winner"
                                value="home"
                                :checked="
                                    game.userPrediction?.penaltyWinner ===
                                    'home'
                                "
                                required
                            />
                            {{ game.home.displayName }}
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="penalty_winner"
                                value="away"
                                :checked="
                                    game.userPrediction?.penaltyWinner ===
                                    'away'
                                "
                            />
                            {{ game.away.displayName }}
                        </label>
                    </div>
                    <InputError :message="errors.penalty_winner" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button type="submit" :disabled="processing">
                        Salvar palpite
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="dashboard()">Voltar ao painel</Link>
                    </Button>
                </div>
            </Form>
        </section>

        <section class="rounded-xl border p-4 md:p-6">
            <Heading
                variant="small"
                title="Palpites"
                :description="
                    game.arePredictionsVisible
                        ? 'Palpites de todos os participantes neste jogo.'
                        : 'Os palpites dos participantes serão revelados quando o prazo encerrar.'
                "
            />

            <div v-if="game.allPredictions?.length" class="mt-4">
                <ol class="space-y-3 text-sm">
                    <li
                        v-for="prediction in game.allPredictions"
                        :key="prediction.userId"
                        class="mb-2 flex flex-col gap-1 rounded-lg p-2 odd:bg-gray-100 sm:flex-row sm:items-start sm:justify-between dark:odd:bg-gray-900/30"
                        :class="{ 'font-semibold': prediction.isCurrentUser }"
                    >
                        <span class="text-center sm:text-left">
                            {{ prediction.userName }}
                            <span
                                v-if="prediction.isCurrentUser"
                                class="font-normal text-muted-foreground"
                            >
                                (você)
                            </span>
                        </span>
                        <div class="text-center sm:text-right">
                            <p class="text-base font-medium sm:text-sm">
                                <template
                                    v-if="
                                        prediction.homeScore !== undefined &&
                                        prediction.awayScore !== undefined
                                    "
                                >
                                    {{ prediction.homeScore }} ×
                                    {{ prediction.awayScore }}
                                    <span
                                        v-if="prediction.penaltyWinner"
                                        class="block text-xs font-normal text-muted-foreground sm:inline sm:text-sm"
                                    >
                                        (pênaltis:
                                        {{
                                            penaltyWinnerLabel(
                                                prediction.penaltyWinner,
                                            )
                                        }})
                                    </span>
                                </template>
                                <template v-else>? × ?</template>
                            </p>
                            <p
                                v-if="
                                    game.arePredictionsVisible &&
                                    prediction.points !== null &&
                                    prediction.points !== undefined
                                "
                                class="text-green-700 dark:text-green-400"
                            >
                                {{ prediction.points }} pontos
                            </p>
                        </div>
                    </li>
                </ol>
            </div>

            <p v-else class="mt-4 text-sm text-muted-foreground">
                Nenhum participante registrou palpite para este jogo.
            </p>
        </section>

        <GameCommentsSection :game-id="game.id" :comments="comments" />
    </div>
</template>
