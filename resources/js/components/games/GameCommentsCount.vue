<script setup lang="ts">
import { MessageCircle } from 'lucide-vue-next';

type Props = {
    count: number;
};

defineProps<Props>();
</script>

<template>
    <span
        class="inline-flex items-center gap-1 text-sm text-muted-foreground"
        :aria-label="`${count} comentário${count === 1 ? '' : 's'}`"
    >
        <MessageCircle class="size-4" aria-hidden="true" />
        <span>{{ count }}</span>
    </span>
</template>
