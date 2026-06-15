<script setup lang="ts">
import { CircleAlert, CircleCheck } from 'lucide-vue-next';
import type { GameResult, UserPrediction } from '@/types/game';

type Props = {
    prediction?: UserPrediction | null;
    isFinal?: boolean;
    result?: GameResult | null;
};

defineProps<Props>();
</script>

<template>
    <div
        class="mt-4 flex flex-col gap-1 rounded-md text-xs font-medium md:text-sm"
    >
        <p class="flex items-center gap-1.5">
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
                    Sem palpite
                </span>
            </template>
        </p>

        <template v-if="isFinal && result">
            <p>
                <span class="text-muted-foreground">Resultado:</span>
                {{ result.homeScore }} × {{ result.awayScore }}
            </p>
            <p
                v-if="
                    prediction?.points !== null &&
                    prediction?.points !== undefined
                "
                class="font-medium text-green-700 dark:text-green-400"
            >
                +{{ prediction.points }} pontos
            </p>
            <p v-else class="text-muted-foreground">—</p>
        </template>
    </div>
</template>
