<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import FeaturedAchievementBadge from '@/components/achievements/FeaturedAchievementBadge.vue';
import { show as showUser } from '@/routes/users';
import type { FeaturedAchievement } from '@/types/achievement';

type Props = {
    userId: number;
    userName: string;
    isCurrentUser?: boolean;
    featuredAchievement?: FeaturedAchievement | null;
};

defineProps<Props>();
</script>

<template>
    <Link
        :href="showUser(userId)"
        class="inline-flex items-center gap-1 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
    >
        <span>{{ userName }}</span>
        <FeaturedAchievementBadge
            v-if="featuredAchievement"
            :achievement="featuredAchievement"
        />
        <span
            v-if="isCurrentUser"
            class="font-normal text-muted-foreground"
        >
            (você)
        </span>
    </Link>
</template>
