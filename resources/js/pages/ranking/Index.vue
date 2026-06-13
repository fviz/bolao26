<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import LeaderboardList from '@/components/LeaderboardList.vue';
import MedalLeaderboardList from '@/components/MedalLeaderboardList.vue';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { index as rankingIndex } from '@/routes/ranking';
import type { LeaderboardEntry, MedalLeaderboardEntry } from '@/types/game';

type Props = {
    leaderboard?: LeaderboardEntry[];
    medalLeaderboard?: MedalLeaderboardEntry[];
};

defineProps<Props>();

const isReady = useHasPageProp('leaderboard', 'medalLeaderboard');

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ranking',
                href: rankingIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Ranking" />

    <div v-if="isReady" class="flex flex-col gap-8 p-4 md:p-6">
        <Heading
            variant="small"
            title="Ranking"
            description="Classificação geral do bolão por pontuação e medalhas"
        />

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-sm font-semibold">Pontuação</h2>
                <LeaderboardList
                    v-if="leaderboard"
                    :entries="leaderboard"
                    show-avatar
                />
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-sm font-semibold">Medalhas</h2>
                <MedalLeaderboardList
                    v-if="medalLeaderboard"
                    :entries="medalLeaderboard"
                    show-avatar
                />
            </section>
        </div>
    </div>
</template>
