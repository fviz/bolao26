<script setup lang="ts">
import { CircleAlert, CircleCheck } from 'lucide-vue-next';
import type { UserPrediction } from '@/types/game';

type Props = {
    prediction?: UserPrediction | null;
};

defineProps<Props>();
</script>

<template>
    <p
        class="flex items-center gap-1.5 rounded-md text-xs font-medium md:text-sm mt-4"
    >
        <template v-if="prediction">
            <CircleCheck
                class="size-3.5 shrink-0 text-green-700 md:size-4 dark:text-green-400"
                aria-hidden="true"
            />
            <span class="text-green-700 dark:text-green-400">
                Seu palpite:
                {{ prediction.homeScore }} ×
                {{ prediction.awayScore }}
            </span>
        </template>
        <template v-else>
            <CircleAlert
                class="size-3.5 shrink-0 text-gray-600 md:size-4 dark:text-gray-400"
                aria-hidden="true"
            />
            <span class="text-gray-600 dark:text-gray-400">
                Palpite ainda não feito
            </span>
        </template>
    </p>
</template>
