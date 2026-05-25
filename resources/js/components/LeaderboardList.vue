<script setup lang="ts">
import type { LeaderboardEntry } from '@/types/game';

withDefaults(
    defineProps<{
        entries: LeaderboardEntry[];
        showYouLabel?: boolean;
    }>(),
    {
        showYouLabel: true,
    },
);
</script>

<template>
    <ol v-if="entries.length" class="space-y-1 text-sm">
        <li
            v-for="entry in entries"
            :key="entry.id"
            class="flex justify-between gap-2"
            :class="{ 'font-semibold': entry.isCurrentUser }"
        >
            <span class="">
                {{ entry.rank }}º {{ entry.name }}
                <span
                    v-if="showYouLabel && entry.isCurrentUser"
                    class="font-normal"
                >
                    (você)
                </span>
            </span>
            <span class="">{{ entry.totalPoints }}</span>
        </li>
    </ol>
    <p v-else class="text-muted-foreground text-sm">
        Nenhuma pontuação ainda.
    </p>
</template>
