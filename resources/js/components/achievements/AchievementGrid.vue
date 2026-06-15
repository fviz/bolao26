<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import SetFeaturedAchievementButton from '@/components/achievements/SetFeaturedAchievementButton.vue';
import { show as showAchievement } from '@/routes/users/achievements';
import type { Achievement } from '@/types/achievement';

type Props = {
    achievements: Achievement[];
    userId: number;
    showProgress?: boolean;
    showFeaturedActions?: boolean;
    featuredAchievementLocked?: boolean;
};

withDefaults(defineProps<Props>(), {
    showProgress: false,
    showFeaturedActions: false,
    featuredAchievementLocked: false,
});
</script>

<template>
    <div
        class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8"
    >
        <div
            v-for="achievement in achievements"
            :key="achievement.slug"
            class="flex flex-col items-center gap-2 rounded-lg p-2 text-center"
            :class="{
                'ring-2 ring-primary ring-offset-2 ring-offset-background':
                    achievement.isFeatured,
            }"
        >
            <Link
                :href="
                    showAchievement({
                        user: userId,
                        achievement: achievement.slug,
                    })
                "
                class="group flex flex-col items-center gap-2 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
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
                        class="text-[10px] font-medium tracking-wide text-muted-foreground/80 uppercase"
                    >
                        {{ achievement.tierLabel }}
                    </span>
                    <span
                        v-if="achievement.isFeatured"
                        class="text-[10px] font-medium tracking-wide text-primary uppercase"
                    >
                        Em destaque
                    </span>
                </div>
            </Link>
            <SetFeaturedAchievementButton
                v-if="showFeaturedActions && achievement.earned"
                :user-id="userId"
                :achievement-slug="achievement.slug"
                :is-featured="achievement.isFeatured ?? false"
                :earned="achievement.earned"
                :locked="featuredAchievementLocked"
                size="sm"
            />
        </div>
    </div>
</template>
