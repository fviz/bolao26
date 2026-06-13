<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import { show as showAchievement } from '@/routes/users/achievements';
import type { Achievement } from '@/types/achievement';

type Props = {
    achievements: Achievement[];
    userId: number;
    showProgress?: boolean;
};

withDefaults(defineProps<Props>(), {
    showProgress: false,
});
</script>

<template>
    <div
        class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8"
    >
        <Link
            v-for="achievement in achievements"
            :key="achievement.slug"
            :href="showAchievement({ user: userId, achievement: achievement.slug })"
            class="group flex flex-col items-center gap-2 rounded-lg p-2 text-center transition-colors hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
            <AchievementEmblem
                :achievement="achievement"
                :show-progress="showProgress"
            />
            <div class="flex flex-col items-center gap-0.5">
                <span
                    class="line-clamp-2 text-xs text-muted-foreground group-hover:text-foreground"
                >
                    {{ achievement.name }}
                </span>
                <span
                    class="text-[10px] font-medium uppercase tracking-wide text-muted-foreground/80"
                >
                    {{ achievement.tierLabel }}
                </span>
            </div>
        </Link>
    </div>
</template>
