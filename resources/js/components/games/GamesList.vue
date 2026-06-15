<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import GameCommentsCount from '@/components/games/GameCommentsCount.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import UserPredictionSummary from '@/components/games/UserPredictionSummary.vue';
import LoadMoreList from '@/components/LoadMoreList.vue';
import { Card, CardContent } from '@/components/ui/card';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { show as showGame } from '@/routes/games';
import type { GameListItem, Paginated } from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
    scrollProp: string;
    emptyMessage: string;
    actionLabel?: string;
    actionLabelWithoutPrediction?: string;
    actionLabelWithPrediction?: string;
    showUserPrediction?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    actionLabel: 'Ver jogo',
    showUserPrediction: false,
});

const { formatScheduledAt, groupGamesByDay } = useGameSchedule();

const gamesByDay = computed(() => groupGamesByDay(props.games.data));

const actionLabelForGame = (game: GameListItem): string => {
    if (props.actionLabelWithoutPrediction && props.actionLabelWithPrediction) {
        return game.userPrediction
            ? props.actionLabelWithPrediction
            : props.actionLabelWithoutPrediction;
    }

    return props.actionLabel;
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <p
            v-if="games.data.length === 0"
            class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground"
        >
            {{ emptyMessage }}
        </p>

        <LoadMoreList v-else :data="scrollProp">
            <div class="flex flex-col gap-6">
                <section
                    v-for="group in gamesByDay"
                    :key="group.key"
                    class="flex flex-col gap-4"
                >
                    <h3 class="text-base font-semibold">
                        {{ group.label }}
                    </h3>

                    <div
                        class="grid grid-cols-1 grid-cols-2 gap-4 lg:grid-cols-3"
                    >
                        <Link
                            v-for="game in group.games"
                            :key="game.id"
                            :href="showGame(game.id)"
                            class="group block rounded-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            :aria-label="`${actionLabelForGame(game)} — ${game.matchTitle}`"
                        >
                            <Card
                                class="h-full cursor-pointer gap-0 bg-gray-100 py-0 transition-colors group-hover:bg-gray-200 last:mb-0 dark:bg-gray-900 dark:group-hover:bg-gray-800"
                            >
                                <CardContent class="flex flex-col gap-2 p-3">
                                    <GameMatchDisplay
                                        :home="game.home"
                                        :away="game.away"
                                    />
                                    <UserPredictionSummary
                                        v-if="showUserPrediction"
                                        :prediction="game.userPrediction"
                                        :is-final="game.isFinal"
                                        :result="game.result"
                                    />
                                    <div class="flex flex-col gap-0.5">
                                        <div
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{
                                                    formatScheduledAt(
                                                        game.scheduledAt,
                                                    ).combined
                                                }}
                                            </p>
                                            <GameCommentsCount
                                                :count="game.commentsCount"
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </section>
            </div>
        </LoadMoreList>
    </div>
</template>
