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
                title: 'Dashboard do Bolão',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard do Bolão" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="flex flex-col justify-center gap-1 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-muted-foreground text-sm">Minha pontuação</p>
                <p class="text-3xl font-bold">{{ userTotalPoints }}</p>
                <p class="text-muted-foreground text-xs">pontos no total</p>
            </div>
            <div
                class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-muted-foreground text-sm">Ranking geral</p>
                <LeaderboardList :entries="leaderboard" />
                <Link
                    :href="rankingIndex()"
                    class="text-primary text-sm font-medium hover:underline"
                >
                    Ver ranking completo
                </Link>
            </div>
            <div
                class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-muted-foreground text-sm">Próximo jogo</p>
                <template v-if="nextGame">
                    <GameMatchDisplay
                        :home="nextGame.home"
                        :away="nextGame.away"
                        layout="stacked"
                    />
                    <p class="text-muted-foreground text-sm">
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
                        class="text-primary text-sm font-medium hover:underline"
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
