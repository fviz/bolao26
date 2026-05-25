<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import UpcomingGamesList from '@/components/games/UpcomingGamesList.vue';
import LeaderboardList from '@/components/LeaderboardList.vue';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { dashboard } from '@/routes';
import { show as showGame } from '@/routes/games';
import { index as rankingIndex } from '@/routes/ranking';
import type {
    GameListItem,
    LeaderboardEntry,
    Paginated,
} from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
    userTotalPoints: number;
    leaderboard: LeaderboardEntry[];
    nextGame: GameListItem | null;
};

defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Painel do Bolão',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Painel do Bolão" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="flex flex-col justify-between gap-1 rounded-xl border border-green-300 p-4 dark:border-green-900 bg-green-50 dark:bg-green-950 text-green-500 dark:text-green-400"
            >
                <div class="">
                    <p class="text-green-700 dark:text-green-400 text-sm">Minha pontuação</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-green-700 dark:text-green-400">{{ userTotalPoints }}</p>
                    <p class="text-green-700 dark:text-green-400 text-xs">pontos no total</p>
                </div>
            </div>
            <div
                class="flex flex-col justify-between gap-2 rounded-xl border border-blue-300 p-4 dark:border-blue-900 bg-blue-50 dark:bg-blue-950 text-blue-500 dark:text-blue-400"
            >
                <div>
                    <p class=" text-sm">Ranking geral</p>
                    <LeaderboardList :entries="leaderboard" class="dark:text-blue-300" />
                </div>
                <div>
                    <Link
                        :href="rankingIndex()"
                        class="text-sm font-medium hover:underline"
                    >
                        Ver ranking completo
                    </Link>
                </div>
                
            </div>
            <div
                class="flex flex-col gap-2 rounded-xl border border-yellow-400 p-4 dark:border-yellow-900 bg-yellow-50 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400"
            >
                <p class="text-sm">Próximo jogo</p>
                <template v-if="nextGame">
                    <GameMatchDisplay
                        :home="nextGame.home"
                        :away="nextGame.away"
                        layout="stacked"
                    />
                    <p class="text-sm">
                        {{
                            formatScheduledAt(nextGame.scheduledAt).combined
                        }}
                    </p>
                    <p
                        v-if="nextGame.userPrediction"
                        class="text-sm font-medium"
                    >
                        Seu palpite:
                        {{ nextGame.userPrediction.homeScore }} ×
                        {{ nextGame.userPrediction.awayScore }}
                    </p>
                    <Link
                        :href="showGame(nextGame.id)"
                        class="text-sm font-medium hover:underline"
                    >
                        Ver jogo
                    </Link>
                </template>
                <p v-else class="text-muted-foreground text-sm">
                    Nenhum jogo programado.
                </p>
            </div>
        </div>

        <div
            class="relative flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <UpcomingGamesList :games="games" />
        </div>
    </div>
</template>
