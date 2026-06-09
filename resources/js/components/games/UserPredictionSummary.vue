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
        class="flex items-center gap-1.5 rounded-md bg-gray-100 p-2 text-sm font-medium dark:bg-gray-800"
    >
        <CircleCheck
            class="size-4 shrink-0 text-green-700 dark:text-green-400"
            aria-hidden="true"
        />
        <span>
            Seu palpite:
            {{ prediction.homeScore }} ×
            {{ prediction.awayScore }}
        </span>
    </p>
</template>
