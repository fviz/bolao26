<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
} from '@/components/ui/card';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { show as showGame } from '@/routes/games';
import type { GameListItem, Paginated } from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
};

defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();

const venueLabel = (game: GameListItem): string => {
    if (game.stadiumName && game.cityName) {
        return `${game.stadiumName}, ${game.cityName}`;
    }

    return game.stadiumName ?? game.cityName ?? '—';
};
</script>

<template>
    <div class="flex flex-col gap-4 p-4 md:p-6">
        <Heading
            variant="small"
            title="Lista de jogos"
            description="Próximos jogos do campeonato"
        />

        <p
            v-if="games.data.length === 0"
            class="text-muted-foreground rounded-xl border border-dashed p-8 text-center text-sm"
        >
            Nenhum jogo programado no momento.
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
                        <p class="text-muted-foreground text-sm">
                            {{ formatScheduledAt(game.scheduledAt).combined }}
                        </p>
                        <p class="text-sm">
                            {{ venueLabel(game) }}
                        </p>
                    </CardContent>
                    <CardFooter class="border-t px-4 py-3">
                        <Button
                            as-child
                            class="min-h-10 w-full"
                            variant="default"
                        >
                            <Link :href="showGame(game.id)"> Ver jogo </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <div
                class="hidden overflow-x-auto rounded-xl border md:block"
            >
                <table class="w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th
                                class="min-w-[12rem] px-4 py-3 text-left font-medium"
                            >
                                Jogo
                            </th>
                            <th
                                class="whitespace-nowrap px-4 py-3 text-left font-medium"
                            >
                                Data e hora
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Estádio
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
                                index % 2 === 1 ? 'bg-muted/20' : 'bg-background'
                            "
                        >
                            <td class="px-4 py-3">
                                <GameMatchDisplay
                                    :home="game.home"
                                    :away="game.away"
                                />
                            </td>
                            <td
                                class="text-muted-foreground whitespace-nowrap px-4 py-3"
                            >
                                {{
                                    formatScheduledAt(game.scheduledAt)
                                        .combined
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="block">{{
                                    game.stadiumName ?? '—'
                                }}</span>
                                <span
                                    v-if="game.cityName"
                                    class="text-muted-foreground block text-xs"
                                    >{{ game.cityName }}</span
                                >
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="showGame(game.id)"
                                    class="text-primary font-medium hover:underline"
                                >
                                    Ver jogo
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav
                v-if="games.meta.last_page > 1"
                class="flex flex-wrap items-center justify-center gap-2 py-2"
                aria-label="Paginação de jogos"
            >
                <Button
                    v-if="games.links.prev"
                    as-child
                    variant="outline"
                    size="sm"
                    class="min-h-10"
                >
                    <Link
                        :href="games.links.prev"
                        preserve-scroll
                    >
                        Anterior
                    </Link>
                </Button>
                <span class="text-muted-foreground px-2 text-sm">
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
                    <Link
                        :href="games.links.next"
                        preserve-scroll
                    >
                        Próxima
                    </Link>
                </Button>
            </nav>
        </div>
    </div>
</template>
