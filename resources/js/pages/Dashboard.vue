<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BellRing, Target, Trophy } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import UpcomingGamesList from '@/components/games/UpcomingGamesList.vue';
import FeaturedGameCard from '@/components/games/FeaturedGameCard.vue';
import LeaderboardList from '@/components/LeaderboardList.vue';
import { ArrowRight } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { useWebPush } from '@/composables/useWebPush';
import { dashboard } from '@/routes';
import { edit as editNotifications } from '@/routes/notifications/settings';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as rankingIndex } from '@/routes/ranking';
import type { FeaturedGame, GameListItem, LeaderboardEntry, Paginated } from '@/types/game';

type Props = {
    games?: Paginated<GameListItem>;
    userTotalPoints?: number;
    leaderboard?: LeaderboardEntry[];
    nextGame?: GameListItem | null;
    featuredGame?: FeaturedGame | null;
    browserPushAvailable?: boolean;
    championPredictionsOpen?: boolean;
    topScorerPredictionsOpen?: boolean;
    hasChampionPrediction?: boolean;
    hasTopScorerPrediction?: boolean;
};

const props = defineProps<Props>();

const isReady = useHasPageProp('games');

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

const showChampionPredictionBanner = computed(
    () => props.championPredictionsOpen && !props.hasChampionPrediction,
);

const showTopScorerPredictionBanner = computed(
    () => props.topScorerPredictionsOpen && !props.hasTopScorerPrediction,
);

onMounted(() => {
    void refreshSubscription();
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Painel',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Painel" />

    <div
        v-if="isReady"
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >

        <Alert v-if="showChampionPredictionBanner">
            <Trophy />
            <AlertTitle>Palpite de campeão pendente</AlertTitle>
            <AlertDescription>
                <p>
                    Você ainda não escolheu a seleção campeã da Copa. Faça seu
                    palpite antes do prazo encerrar.
                </p>
                <Button as-child size="sm" class="mt-3">
                    <Link :href="predictionsIndex()">
                        Fazer palpite
                    </Link>
                </Button>
            </AlertDescription>
        </Alert>

        <Alert v-if="showTopScorerPredictionBanner">
            <Target />
            <AlertTitle>Palpite de artilheiro pendente</AlertTitle>
            <AlertDescription>
                <p>
                    Você ainda não escolheu o artilheiro da Copa. Faça seu
                    palpite antes do prazo encerrar.
                </p>
                <Button as-child size="sm" class="mt-3">
                    <Link :href="predictionsIndex()">
                        Fazer palpite
                    </Link>
                </Button>
            </AlertDescription>
        </Alert>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-8">
            <div
                class="flex flex-col justify-between gap-1 rounded-xl shadow-sm p-4 bg-green-700 text-green-100 dark:bg-green-950 dark:text-green-100"
            >
                <div class="">
                    <p class="text-sm">
                        Minha pontuação
                    </p>
                </div>
                <div>
                    <p
                        class="text-4xl font-black"
                    >
                        {{ userTotalPoints }}
                    </p>
                    <p class="text-xs">
                        pontos no total
                    </p>
                </div>
            </div>
            <div
                class="flex flex-col justify-between gap-2 rounded-xl p-4 bg-blue-700 text-blue-200 dark:bg-blue-950 dark:text-blue-100"
            >
                <div>
                    <p class="text-sm">Ranking geral</p>
                    <LeaderboardList
                        v-if="leaderboard"
                        :entries="leaderboard"
                        class=""
                    />
                </div>
                <div class="flex justify-end">
                    <Link
                        :href="rankingIndex()"
                        class="text-sm font-medium hover:underline flex items-center gap-1"
                    >
                        <ArrowRight class="size-4" /> Ver ranking completo
                    </Link>
                </div>
            </div>
            <FeaturedGameCard
                v-if="featuredGame"
                :featured-game="featuredGame"
            />
            <div
                v-else
                class="flex flex-col justify-between gap-2 rounded-xl bg-yellow-50 p-4 dark:bg-yellow-950"
            >
                <p class="text-sm">Jogos recentes</p>
                <p class="text-xs text-yellow-700 dark:text-yellow-400">
                    Nenhum jogo em andamento ou finalizado.
                </p>
            </div>
        </div>

        <UpcomingGamesList v-if="games" :games="games" />

    </div>
</template>
