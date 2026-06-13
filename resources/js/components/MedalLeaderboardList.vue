<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import UserProfileLink from '@/components/users/UserProfileLink.vue';
import { useInitials } from '@/composables/useInitials';
import type { MedalLeaderboardEntry } from '@/types/game';

withDefaults(
    defineProps<{
        entries: MedalLeaderboardEntry[];
        showYouLabel?: boolean;
        showAvatar?: boolean;
    }>(),
    {
        showYouLabel: true,
        showAvatar: false,
    },
);

const { getInitials } = useInitials();

const tierBadges = [
    {
        key: 'diamondCount' as const,
        emoji: '💎',
        className: 'text-cyan-400',
    },
    {
        key: 'goldCount' as const,
        emoji: '🥇',
        className: 'text-yellow-500',
    },
    {
        key: 'silverCount' as const,
        emoji: '🥈',
        className: 'text-slate-400',
    },
    {
        key: 'bronzeCount' as const,
        emoji: '🥉',
        className: 'text-amber-700',
    },
];
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
                    {{ entry.rank }}º
                    <UserProfileLink
                        :user-id="entry.id"
                        :user-name="entry.name"
                        :is-current-user="entry.isCurrentUser && showYouLabel"
                    />
                </span>
            </span>
            <span class="flex shrink-0 items-center gap-2">
                <template
                    v-for="tier in tierBadges"
                    :key="tier.key"
                >
                    <span
                        v-if="entry[tier.key] > 0"
                        class="inline-flex items-center gap-0.5 tabular-nums"
                        :class="tier.className"
                    >
                        <span role="img" :aria-hidden="true">{{ tier.emoji }}</span>
                        {{ entry[tier.key] }}
                    </span>
                </template>
            </span>
        </li>
    </ol>
    <p v-else class="text-muted-foreground text-sm">
        Nenhuma medalha conquistada ainda.
    </p>
</template>
