<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDownRight } from 'lucide-vue-next';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { show as showGame } from '@/routes/games';
import type { FeaturedGame } from '@/types/game';

type Props = {
    featuredGame: FeaturedGame;
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

const schedule = formatScheduledAt(props.featuredGame.game.scheduledAt);
</script>

<template>
    <Link
        :href="showGame(featuredGame.game.id)"
        class="group relative flex flex-col justify-between gap-2 rounded-xl bg-emerald-700 p-4 transition-colors hover:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:bg-emerald-950 dark:hover:bg-emerald-900"
        :aria-label="`Ver jogo — ${featuredGame.game.matchTitle}`"
    >
        <div class="flex flex-col gap-2 text-emerald-200 dark:text-white">
            <div class="flex items-center justify-between gap-2">
                <span
                    v-if="featuredGame.status === 'live'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-200 dark:text-emerald-400"
                >
                    <span
                        class="size-2 animate-pulse rounded-full bg-emerald-200 dark:bg-emerald-400"
                    />
                    Ao vivo
                </span>
                <span
                    v-if="featuredGame.status !== 'live'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-200 dark:text-emerald-400"
                >
                    Resultado recente
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
            </div>
        </div>

        <ArrowDownRight
            class="absolute right-2 bottom-2 size-4 text-emerald-200 opacity-80 transition-opacity group-hover:opacity-100 dark:text-white"
        />
    </Link>
</template>
