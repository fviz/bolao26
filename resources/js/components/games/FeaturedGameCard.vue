<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { show as showGame } from '@/routes/games';
import { ArrowRight } from 'lucide-vue-next';
import type { FeaturedGame } from '@/types/game';

type Props = {
    featuredGame: FeaturedGame;
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

const schedule = formatScheduledAt(props.featuredGame.game.scheduledAt);

const stageLabel = [props.featuredGame.game.stageName, props.featuredGame.game.groupName]
    .filter(Boolean)
    .join(' · ');
</script>

<template>
    <div
        class="flex flex-col justify-between gap-2 rounded-xl bg-emerald-700 p-4 dark:bg-emerald-950"
    >
        <div class="flex flex-col gap-2 text-emerald-200 dark:text-white">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm">
                    {{
                        featuredGame.status === 'live'
                            ? 'Em andamento'
                            : 'Último resultado'
                    }}
                </p>
                <span
                    v-if="featuredGame.status === 'live'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-yellow-700 dark:text-yellow-400"
                >
                    <span
                        class="size-2 animate-pulse rounded-full bg-yellow-600 dark:bg-yellow-400"
                    />
                    Ao vivo
                </span>
            </div>

            <GameMatchDisplay
                :home="featuredGame.game.home"
                :away="featuredGame.game.away"
                layout="inline"
            />

            <p
                v-if="featuredGame.status === 'finished' && featuredGame.game.result"
                class="text-2xl font-black text-emerald-200 dark:text-white"
            >
                {{ featuredGame.game.result.homeScore }}
                ×
                {{ featuredGame.game.result.awayScore }}
            </p>

            <div class="flex flex-col gap-0.5 text-xs">
                <p>{{ schedule.combined }}</p>
                <p v-if="stageLabel">{{ stageLabel }}</p>
            </div>
        </div>

        <div class="flex justify-end">
            <Link
                :href="showGame(featuredGame.game.id)"
                class="text-sm font-medium hover:underline text-emerald-200 dark:text-white flex items-center gap-1"
            >
                <ArrowRight class="size-4 text-emerald-200 dark:text-white" />
                Ver jogo
            </Link>
        </div>
    </div>
</template>
