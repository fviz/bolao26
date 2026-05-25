<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    code: string | null;
}>();

const flagCode = computed(() => props.code?.trim().toLowerCase() || null);
</script>

<template>
    <span
        v-if="flagCode"
        class="fi shrink-0 rounded-[1px] text-lg leading-none shadow-xs ring-1 ring-black/10 dark:ring-white/20"
        :class="`fi-${flagCode}`"
        aria-hidden="true"
    />
</template>
