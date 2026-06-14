<script setup lang="ts">
import type { AchievementTier, FeaturedAchievement } from '@/types/achievement';

type Props = {
    achievement: FeaturedAchievement;
    size?: 'xs' | 'md';
};

withDefaults(defineProps<Props>(), {
    size: 'xs',
});

const tierClasses: Record<AchievementTier, string> = {
    lixo_humano: 'ring-stone-600/80 bg-stone-950/30',
    bronze: 'ring-amber-700/80 bg-amber-950/20',
    silver: 'ring-slate-400 bg-slate-900/20',
    gold: 'ring-yellow-500 bg-yellow-950/20',
    diamond: 'ring-cyan-400 bg-cyan-950/20',
};

const sizeClasses = {
    xs: 'size-5 text-xs ring-1',
    md: 'size-10 text-xl ring-2',
};
</script>

<template>
    <span
        class="inline-flex shrink-0 items-center justify-center rounded-full"
        :class="[tierClasses[achievement.tier], sizeClasses[size]]"
        :title="achievement.name"
        role="img"
        :aria-label="achievement.name"
    >
        {{ achievement.emoji }}
    </span>
</template>
