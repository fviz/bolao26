<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import UserProfileLink from '@/components/users/UserProfileLink.vue';
import { useInitials } from '@/composables/useInitials';
import type { LeaderboardEntry } from '@/types/game';

withDefaults(
    defineProps<{
        entries: LeaderboardEntry[];
        showYouLabel?: boolean;
        showAvatar?: boolean;
    }>(),
    {
        showYouLabel: true,
        showAvatar: false,
    },
);

const { getInitials } = useInitials();
</script>

<template>
    <ol v-if="entries.length" class="space-y-1 text-sm">
        <li
            v-for="entry in entries"
            :key="entry.id"
            class="flex items-center justify-between gap-3"
            :class="{ 'font-semibold': entry.isCurrentUser }"
        >
            <span class="flex min-w-0 items-center gap-2">
                <Avatar
                    v-if="showAvatar"
                    class="size-8 border bg-muted"
                >
                    <AvatarImage
                        v-if="entry.avatar"
                        :src="entry.avatar"
                        :alt="entry.name"
                    />
                    <AvatarFallback
                        class="rounded-full text-xs font-medium text-black dark:text-white"
                    >
                        {{ getInitials(entry.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="flex min-w-0 items-center gap-1">
                    <span class="shrink-0">{{ entry.rank }}º</span>
                    <UserProfileLink
                        :user-id="entry.id"
                        :user-name="entry.name"
                        :is-current-user="entry.isCurrentUser && showYouLabel"
                        :featured-achievement="entry.featuredAchievement"
                    />
                </span>
            </span>
            <span class="shrink-0">{{ entry.totalPoints }}</span>
        </li>
    </ol>
    <p v-else class="text-muted-foreground text-sm">
        Nenhuma pontuação ainda.
    </p>
</template>
