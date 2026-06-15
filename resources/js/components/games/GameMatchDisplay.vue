<script setup lang="ts">
import FlagIcon from '@/components/FlagIcon.vue';
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
                ? 'flex flex-col items-center gap-1 rounded-lg border py-1.5 text-center [&_.fi]:text-base md:gap-2 md:rounded-xl md:py-2 md:[&_.fi]:text-lg'
                : 'flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-xs'
        "
    >
        <span class="inline-flex min-w-0 items-center gap-1.5">
            <FlagIcon v-if="home.flagIconCode" :code="home.flagIconCode" />
            <span
                v-else-if="home.abbr"
                class="shrink-0 rounded bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground"
                >{{ home.abbr }}</span
            >
            <span class="truncate font-medium">{{ home.abbr }}</span>
        </span>
        <span
            class="shrink-0 text-sm text-muted-foreground"
            :class="layout === 'stacked' ? '' : 'px-0.5'"
            >×</span
        >
        <span class="inline-flex min-w-0 items-center gap-1.5">
            <FlagIcon v-if="away.flagIconCode" :code="away.flagIconCode" />
            <span
                v-else-if="away.abbr"
                class="shrink-0 rounded bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground"
                >{{ away.abbr }}</span
            >
            <span class="truncate font-medium">{{ away.abbr }}</span>
        </span>
    </div>
</template>
