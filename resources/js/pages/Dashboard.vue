<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowDownRight, Target, Trophy } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import FeaturedGameCard from '@/components/games/FeaturedGameCard.vue';
import UpcomingGamesList from '@/components/games/UpcomingGamesList.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { useWebPush } from '@/composables/useWebPush';
import { dashboard } from '@/routes';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as rankingIndex } from '@/routes/ranking';
import { index as achievementsIndex } from '@/routes/users/achievements';
import type { Achievement } from '@/types/achievement';
import type { FeaturedGame, GameListItem, Paginated } from '@/types/game';

type Props = {
    games?: Paginated<GameListItem>;
    userTotalPoints?: number;
    userRank?: number;
    nextGame?: GameListItem | null;
    featuredGame?: FeaturedGame | null;
    championPredictionsOpen?: boolean;
    topScorerPredictionsOpen?: boolean;
    hasChampionPrediction?: boolean;
    hasTopScorerPrediction?: boolean;
    latestAchievement?: Achievement | null;
    earnedAchievementsCount?: number;
};

const props = defineProps<Props>();

const page = usePage();

const isReady = useHasPageProp('games');

const additionalMedalsCount = computed(() => {
    if (!props.latestAchievement) {
        return 0;
    }

    return Math.max(0, (props.earnedAchievementsCount ?? 1) - 1);
});

const additionalMedalsText = computed(() => {
    if (additionalMedalsCount.value === 1) {
        return ' e mais 1 medalha';
    }

    if (additionalMedalsCount.value > 1) {
        return ` e mais ${additionalMedalsCount.value} medalhas`;
    }

    return null;
});

const tileLinkClass =
    'group relative flex flex-col justify-between gap-1 rounded-xl p-4 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

const { refreshSubscription } = useWebPush();

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

        <div class="grid auto-rows-min gap-4 grid-cols-2 mb-8">
            <Link
                :href="rankingIndex()"
                :class="[
                    tileLinkClass,
                    'shadow-sm bg-emerald-700 text-emerald-100 hover:bg-emerald-600 dark:bg-emerald-950 dark:text-emerald-100 dark:hover:bg-emerald-900',
                ]"
                aria-label="Minha pontuação"
            >
                <div>
                    <p class="text-sm">
                        Minha pontuação
                    </p>
                </div>
                <div>
                    <p class="text-4xl font-black">
                        {{ userTotalPoints }}
                    </p>
                    <p class="text-xs">
                        pontos no total
                    </p>
                </div>
                <ArrowDownRight
                    class="absolute right-2 bottom-2 size-4 opacity-80 transition-opacity group-hover:opacity-100"
                />
            </Link>
            <Link
                :href="rankingIndex()"
                :class="[
                    tileLinkClass,
                    'bg-emerald-700 text-emerald-200 hover:bg-emerald-600 dark:bg-emerald-950 dark:text-emerald-100 dark:hover:bg-emerald-900',
                ]"
                aria-label="Ranking geral"
            >
                <div>
                    <p class="text-sm">Ranking geral</p>
                </div>
                <div>
                    <p class="text-4xl font-black">
                        {{ userRank }}º
                    </p>
                    <p class="text-xs">
                        posição no bolão
                    </p>
                </div>
                <ArrowDownRight
                    class="absolute right-2 bottom-2 size-4 opacity-80 transition-opacity group-hover:opacity-100"
                />
            </Link>
            <FeaturedGameCard
                v-if="featuredGame"
                :featured-game="featuredGame"
            />
            <div
                v-else
                class="flex flex-col justify-between gap-2 rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950"
            >
                <p class="text-sm">Jogos recentes</p>
                <p class="text-xs text-emerald-700 dark:text-emerald-400">
                    Nenhum jogo em andamento ou finalizado.
                </p>
            </div>
            <Link
                :href="achievementsIndex(page.props.auth.user.id)"
                :class="[
                    tileLinkClass,
                    'gap-2 bg-emerald-700 text-emerald-100 hover:bg-emerald-600 dark:bg-emerald-950 dark:hover:bg-emerald-900',
                ]"
                aria-label="Medalhas"
            >
                <p class="text-sm">Medalhas</p>
                <div
                    v-if="latestAchievement"
                    class="flex items-center gap-3"
                >
                    <AchievementEmblem
                        :achievement="{
                            ...latestAchievement,
                            earned: true,
                            progressCurrent: null,
                            progressTarget: null,
                        }"
                        size="sm"
                    />
                    <div>
                        <p class="text-sm font-semibold leading-tight">
                            {{ latestAchievement.name }}<br>
                            <span
                                v-if="additionalMedalsText"
                                class="text-xs font-normal text-emerald-200 dark:text-emerald-400"
                            >
                                {{ additionalMedalsText }}
                            </span>
                        </p>
                        <p
                            v-if="additionalMedalsCount === 0"
                            class="text-xs text-emerald-200 dark:text-emerald-400"
                        >
                            {{ latestAchievement.tierLabel }}
                        </p>
                    </div>
                </div>
                <p v-else class="text-xs text-emerald-200 dark:text-emerald-400">
                    Você ainda não tem nenhuma medalha
                </p>
                <ArrowDownRight
                    class="absolute right-2 bottom-2 size-4 opacity-80 transition-opacity group-hover:opacity-100"
                />
            </Link>
        </div>

        <UpcomingGamesList v-if="games" :games="games" />

    </div>
</template>
