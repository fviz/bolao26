<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import LeaderboardList from '@/components/LeaderboardList.vue';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { index as rankingIndex } from '@/routes/ranking';
import type { LeaderboardEntry } from '@/types/game';

type Props = {
    leaderboard?: LeaderboardEntry[];
};

defineProps<Props>();

const isReady = useHasPageProp('leaderboard');

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
            description="Classificação geral do bolão por pontuação"
        />

        <div
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <LeaderboardList
                v-if="leaderboard"
                :entries="leaderboard"
                show-avatar
            />
        </div>
    </div>
</template>
