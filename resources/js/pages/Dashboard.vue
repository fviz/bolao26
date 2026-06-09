<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BellRing } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import GameCommentsCount from '@/components/games/GameCommentsCount.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import UpcomingGamesList from '@/components/games/UpcomingGamesList.vue';
import LeaderboardList from '@/components/LeaderboardList.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { useWebPush } from '@/composables/useWebPush';
import { dashboard } from '@/routes';
import { show as showGame } from '@/routes/games';
import { edit as editNotifications } from '@/routes/notifications/settings';
import { index as rankingIndex } from '@/routes/ranking';
import type { GameListItem, LeaderboardEntry, Paginated } from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
    userTotalPoints: number;
    leaderboard: LeaderboardEntry[];
    nextGame: GameListItem | null;
    browserPushAvailable: boolean;
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();
const {
    isSupported,
    permission,
    isSubscribed,
    refreshSubscription,
} = useWebPush();

const showNotificationsPrompt = computed(
    () =>
        props.browserPushAvailable
        && isSupported.value
        && permission.value !== 'denied'
        && !isSubscribed.value,
);

onMounted(() => {
    void refreshSubscription();
});

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
        <Alert v-if="showNotificationsPrompt">
            <BellRing />
            <AlertTitle>Ative as notificações do navegador</AlertTitle>
            <AlertDescription>
                <p>
                    Receba lembretes e resultados mesmo com o app fechado.
                </p>
                <Button as-child size="sm" class="mt-3">
                    <Link :href="editNotifications()">
                        Habilitar neste dispositivo
                    </Link>
                </Button>
            </AlertDescription>
        </Alert>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="flex flex-col justify-between gap-1 rounded-xl border p-4"
            >
                <div class="">
                    <p class="text-sm">
                        Minha pontuação
                    </p>
                </div>
                <div>
                    <p
                        class="text-4xl font-black text-green-700 dark:text-green-400"
                    >
                        {{ userTotalPoints }}
                    </p>
                    <p class="text-xs text-green-700 dark:text-green-400">
                        pontos no total
                    </p>
                </div>
            </div>
            <div
                class="flex flex-col justify-between gap-2 rounded-xl border p-4"
            >
                <div>
                    <p class="text-sm">Ranking geral</p>
                    <LeaderboardList
                        :entries="leaderboard"
                        class=""
                    />
                </div>
                <div>
                    <Link
                        :href="rankingIndex()"
                        class="text-sm font-medium hover:underline text-blue-500 dark:text-blue-400"
                    >
                        Ver ranking completo
                    </Link>
                </div>
            </div>
            <div
                class="flex flex-col gap-2 rounded-xl border p-4"
            >
                <p class="text-sm">Próximo jogo</p>
                <template v-if="nextGame">
                    <GameMatchDisplay
                        :home="nextGame.home"
                        :away="nextGame.away"
                        layout="stacked"
                    />
                    <div class="flex justify-between items-center gap-2">
                        <p class="text-sm">
                            {{ formatScheduledAt(nextGame.scheduledAt).combined }}
                        </p>
                        <GameCommentsCount :count="nextGame.commentsCount" />
                    </div>
                    <p
                        v-if="nextGame.userPrediction"
                        class="text-sm font-medium bg-gray-100 dark:bg-gray-800 rounded-md p-2"
                    >
                        Seu palpite:
                        {{ nextGame.userPrediction.homeScore }} ×
                        {{ nextGame.userPrediction.awayScore }}
                    </p>
                    <Link
                        :href="showGame(nextGame.id)"
                        class="text-sm font-medium hover:underline text-yellow-500 dark:text-yellow-400"
                    >
                        Ver jogo
                    </Link>
                </template>
                <p v-else class="text-sm text-muted-foreground">
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
