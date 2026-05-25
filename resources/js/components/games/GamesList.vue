<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GameCommentsCount from '@/components/games/GameCommentsCount.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { show as showGame } from '@/routes/games';
import type { GameListItem, Paginated } from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
    emptyMessage: string;
    actionLabel?: string;
    showPredictionColumn?: boolean;
    paginationAriaLabel?: string;
};

withDefaults(defineProps<Props>(), {
    actionLabel: 'Ver jogo',
    showPredictionColumn: false,
    paginationAriaLabel: 'Paginação de jogos',
});

const { formatScheduledAt } = useGameSchedule();

const venueLabel = (game: GameListItem): string => {
    if (game.stadiumName && game.cityName) {
        return `${game.stadiumName}, ${game.cityName}`;
    }

    return game.stadiumName ?? game.cityName ?? '—';
};

const predictionLabel = (game: GameListItem): string => {
    if (!game.userPrediction) {
        return '—';
    }

    return `${game.userPrediction.homeScore} × ${game.userPrediction.awayScore}`;
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

        <div v-else class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 md:hidden">
                <Card
                    v-for="game in games.data"
                    :key="game.id"
                    class="gap-0 py-0"
                >
                    <CardContent class="flex flex-col gap-3 p-4">
                        <GameMatchDisplay
                            :home="game.home"
                            :away="game.away"
                            layout="stacked"
                        />
                        <p
                            v-if="showPredictionColumn"
                            class="text-lg font-semibold"
                        >
                            {{ predictionLabel(game) }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ formatScheduledAt(game.scheduledAt).combined }}
                        </p>
                        <p class="text-sm">
                            {{ venueLabel(game) }}
                        </p>
                        <GameCommentsCount :count="game.commentsCount" />
                    </CardContent>
                    <CardFooter class="border-t px-4 py-3">
                        <Button
                            as-child
                            class="min-h-10 w-full"
                            variant="default"
                        >
                            <Link :href="showGame(game.id)">
                                {{ actionLabel }}
                            </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <div class="hidden overflow-x-auto rounded-xl border md:block">
                <table class="w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th
                                class="min-w-[12rem] px-4 py-3 text-left font-medium"
                            >
                                Jogo
                            </th>
                            <th
                                v-if="showPredictionColumn"
                                class="px-4 py-3 text-left font-medium whitespace-nowrap"
                            >
                                Previsão
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium whitespace-nowrap"
                            >
                                Data e hora
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Estádio
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium whitespace-nowrap"
                            >
                                Comentários
                            </th>
                            <th class="w-28 px-4 py-3 text-right font-medium">
                                <span class="sr-only">Ação</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(game, index) in games.data"
                            :key="game.id"
                            class="border-b transition-colors last:border-b-0 hover:bg-muted/30"
                            :class="
                                index % 2 === 1
                                    ? 'bg-muted/20'
                                    : 'bg-background'
                            "
                        >
                            <td class="px-4 py-3">
                                <GameMatchDisplay
                                    :home="game.home"
                                    :away="game.away"
                                />
                            </td>
                            <td
                                v-if="showPredictionColumn"
                                class="px-4 py-3 font-semibold whitespace-nowrap"
                            >
                                {{ predictionLabel(game) }}
                            </td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    formatScheduledAt(game.scheduledAt).combined
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="block">{{
                                    game.stadiumName ?? '—'
                                }}</span>
                                <span
                                    v-if="game.cityName"
                                    class="block text-xs text-muted-foreground"
                                    >{{ game.cityName }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <GameCommentsCount
                                    :count="game.commentsCount"
                                />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="showGame(game.id)"
                                    class="font-medium text-primary hover:underline"
                                >
                                    {{ actionLabel }}
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav
                v-if="games.meta.last_page > 1"
                class="flex flex-wrap items-center justify-center gap-2 py-2"
                :aria-label="paginationAriaLabel"
            >
                <Button
                    v-if="games.links.prev"
                    as-child
                    variant="outline"
                    size="sm"
                    class="min-h-10"
                >
                    <Link :href="games.links.prev" preserve-scroll>
                        Anterior
                    </Link>
                </Button>
                <span class="px-2 text-sm text-muted-foreground">
                    Página {{ games.meta.current_page }} de
                    {{ games.meta.last_page }}
                </span>
                <Button
                    v-if="games.links.next"
                    as-child
                    variant="outline"
                    size="sm"
                    class="min-h-10"
                >
                    <Link :href="games.links.next" preserve-scroll>
                        Próxima
                    </Link>
                </Button>
            </nav>
        </div>
    </div>
</template>
