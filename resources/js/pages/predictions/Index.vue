<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import GamesList from '@/components/games/GamesList.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { upsert as upsertChampionPrediction } from '@/routes/champion-prediction';
import { index as predictionsIndex } from '@/routes/predictions';
import type {
    ChampionPrediction,
    ChampionTeam,
    GameListItem,
    Paginated,
} from '@/types/game';

type Props = {
    predictedGames: Paginated<GameListItem>;
    missingGames: Paginated<GameListItem>;
    championPrediction: ChampionPrediction | null;
    championPredictionsOpen: boolean;
    championPredictionsDeadline: string;
    championTeams: ChampionTeam[];
};

defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

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
                <p class="text-muted-foreground text-sm">Campeão</p>
                <p v-if="championPrediction" class="font-medium">
                    {{
                        championTeams.find(
                            (t) =>
                                t.fifaTeamId ===
                                championPrediction?.fifaTeamId,
                        )?.name ?? championPrediction.fifaTeamId
                    }}
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
        </div>

        <section
            v-if="championPredictionsOpen && championTeams.length"
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <h2 class="mb-3 text-lg font-semibold">Palpite de campeão</h2>
            <Form
                v-bind="upsertChampionPrediction.form()"
                class="flex flex-wrap items-end gap-4"
                v-slot="{ errors, processing }"
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
                    Salvar campeão
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
