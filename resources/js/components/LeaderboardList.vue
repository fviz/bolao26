<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
                <span class="min-w-0 truncate">
                    {{ entry.rank }}º {{ entry.name }}
                    <span
                        v-if="showYouLabel && entry.isCurrentUser"
                        class="font-normal"
                    >
                        (você)
                    </span>
                </span>
            </span>
            <span class="shrink-0">{{ entry.totalPoints }}</span>
        </li>
    </ol>
    <p v-else class="text-muted-foreground text-sm">
        Nenhuma pontuação ainda.
    </p>
</template>
