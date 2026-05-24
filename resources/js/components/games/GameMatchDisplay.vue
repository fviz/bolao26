<script setup lang="ts">
import type { GameTeam } from '@/types/game';

type Props = {
    home: GameTeam;
    away: GameTeam;
    layout?: 'inline' | 'stacked';
};

withDefaults(defineProps<Props>(), {
    layout: 'inline',
});
</script>

<template>
    <div
        :class="
            layout === 'stacked'
                ? 'flex flex-col items-center gap-2 text-center'
                : 'flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1'
        "
    >
        <span class="inline-flex min-w-0 items-center gap-1.5">
            <span
                v-if="home.flagEmoji"
                class="shrink-0 text-lg leading-none"
                aria-hidden="true"
                >{{ home.flagEmoji }}</span
            >
            <span
                v-else-if="home.abbr"
                class="bg-muted text-muted-foreground shrink-0 rounded px-1.5 py-0.5 text-xs font-medium"
                >{{ home.abbr }}</span
            >
            <span class="truncate font-medium">{{ home.displayName }}</span>
        </span>
        <span
            class="text-muted-foreground shrink-0 text-sm"
            :class="layout === 'stacked' ? '' : 'px-0.5'"
            >×</span
        >
        <span class="inline-flex min-w-0 items-center gap-1.5">
            <span
                v-if="away.flagEmoji"
                class="shrink-0 text-lg leading-none"
                aria-hidden="true"
                >{{ away.flagEmoji }}</span
            >
            <span
                v-else-if="away.abbr"
                class="bg-muted text-muted-foreground shrink-0 rounded px-1.5 py-0.5 text-xs font-medium"
                >{{ away.abbr }}</span
            >
            <span class="truncate font-medium">{{ away.displayName }}</span>
        </span>
    </div>
</template>
