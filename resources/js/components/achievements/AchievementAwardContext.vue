<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlagIcon from '@/components/FlagIcon.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import { show as showGame } from '@/routes/games';
import type { AchievementAwardContext } from '@/types/achievement';

type Props = {
    context: AchievementAwardContext;
    profileName: string;
    isCurrentUser: boolean;
};

const props = defineProps<Props>();

const subject = computed(() =>
    props.isCurrentUser ? 'Você conquistou' : `${props.profileName} conquistou`,
);

const formattedMatchDay = computed(() => {
    if (props.context.type !== 'match_day') {
        return null;
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'long',
    }).format(new Date(`${props.context.matchDay}T12:00:00`));
});

function gameLinkClass(): string {
    return 'inline-flex max-w-full items-center rounded-md font-medium text-foreground underline decoration-muted-foreground/50 underline-offset-4 transition-colors hover:decoration-foreground';
}
</script>

<template>
    <div class="space-y-3 text-sm text-muted-foreground">
        <template v-if="context.type === 'game'">
            <p>
                {{ subject }}
                {{
                    context.trigger === 'on_prediction'
                        ? 'esta medalha ao palpitar'
                        : 'esta medalha após a partida'
                }}
                <Link
                    :href="showGame(context.game.id)"
                    :class="gameLinkClass()"
                >
                    <GameMatchDisplay
                        :home="context.game.home"
                        :away="context.game.away"
                    />
                </Link>
            </p>
        </template>

        <template v-else-if="context.type === 'match_day'">
            <p>
                {{ subject }} esta medalha após os jogos de
                {{ formattedMatchDay }}.
            </p>
            <ul
                v-if="context.games.length > 0"
                class="flex flex-col gap-2 rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
            >
                <li v-for="game in context.games" :key="game.id">
                    <Link :href="showGame(game.id)" :class="gameLinkClass()">
                        <GameMatchDisplay :home="game.home" :away="game.away" />
                    </Link>
                </li>
            </ul>
        </template>

        <template v-else-if="context.type === 'champion'">
            <p class="inline-flex flex-wrap items-center justify-center gap-1.5">
                <span>{{ subject }} esta medalha ao acertar o campeão:</span>
                <span class="inline-flex items-center gap-1.5 font-medium text-foreground">
                    <FlagIcon
                        v-if="context.team.flagIconCode"
                        :code="context.team.flagIconCode"
                    />
                    <span>{{ context.team.name }}</span>
                </span>
            </p>
        </template>

        <template v-else-if="context.type === 'top_scorer'">
            <p>
                {{ subject }} esta medalha ao acertar o artilheiro:
                <span class="font-medium text-foreground">
                    {{ context.player.name }}
                </span>
            </p>
        </template>

        <template v-else-if="context.type === 'champion_and_top_scorer'">
            <p>
                {{ subject }} esta medalha ao acertar o campeão
                <span class="inline-flex items-center gap-1.5 font-medium text-foreground">
                    <FlagIcon
                        v-if="context.team.flagIconCode"
                        :code="context.team.flagIconCode"
                    />
                    <span>{{ context.team.name }}</span>
                </span>
                e o artilheiro
                <span class="font-medium text-foreground">
                    {{ context.player.name }}
                </span>
                .
            </p>
        </template>
    </div>
</template>
