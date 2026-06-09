<script setup lang="ts">
import { CircleCheck } from 'lucide-vue-next';
import type { UserPrediction } from '@/types/game';

type Props = {
    prediction: UserPrediction;
};

defineProps<Props>();
</script>

<template>
    <p
        class="flex items-center gap-1.5 rounded-md bg-gray-100 p-1.5 text-xs font-medium md:p-2 md:text-sm dark:bg-gray-800"
    >
        <CircleCheck
            class="size-3.5 shrink-0 text-green-700 md:size-4 dark:text-green-400"
            aria-hidden="true"
        />
        <span>
            Seu palpite:
            {{ prediction.homeScore }} ×
            {{ prediction.awayScore }}
        </span>
    </p>
</template>
