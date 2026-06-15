<script setup lang="ts">
import { computed } from 'vue';
import type { Achievement, AchievementTier } from '@/types/achievement';

type Props = {
    achievement: Pick<
        Achievement,
        'emoji' | 'tier' | 'earned' | 'progressCurrent' | 'progressTarget'
    >;
    size?: 'sm' | 'md' | 'lg';
    showProgress?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showProgress: false,
});

const tierClasses: Record<AchievementTier, string> = {
    lixo_humano: 'ring-stone-600/80 bg-stone-950/30',
    bronze: 'ring-amber-700/80 bg-amber-950/20',
    silver: 'ring-slate-400 bg-slate-900/20',
    gold: 'ring-yellow-500 bg-yellow-950/20',
    diamond: 'ring-cyan-400 bg-cyan-950/20',
};

const sizeClasses = {
    sm: 'size-14 text-xl ring-2',
    md: 'size-20 text-3xl ring-[3px]',
    lg: 'size-28 text-5xl ring-4',
};

const progressPercent = computed(() => {
    if (
        props.achievement.progressTarget === null ||
        props.achievement.progressCurrent === null ||
        props.achievement.progressTarget === 0
    ) {
        return 0;
    }

    return Math.min(
        100,
        (props.achievement.progressCurrent / props.achievement.progressTarget) *
            100,
    );
});
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <div
            class="flex items-center justify-center rounded-full ring-offset-2 ring-offset-background transition-[filter,opacity]"
            :class="[
                tierClasses[achievement.tier],
                sizeClasses[size],
                achievement.earned ? '' : 'opacity-50 grayscale',
            ]"
        >
            <span role="img" :aria-label="achievement.emoji">{{
                achievement.emoji
            }}</span>
        </div>

        <div
            v-if="
                showProgress &&
                achievement.progressTarget !== null &&
                !achievement.earned
            "
            class="h-1.5 w-full max-w-[5rem] overflow-hidden rounded-full bg-muted"
        >
            <div
                class="h-full rounded-full bg-primary transition-all"
                :style="{ width: `${progressPercent}%` }"
            />
        </div>
    </div>
</template>
