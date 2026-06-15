<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GamesList from '@/components/games/GamesList.vue';
import Heading from '@/components/Heading.vue';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { index as gamesIndex } from '@/routes/games';
import type { GameListItem, Paginated } from '@/types/game';

type Props = {
    games?: Paginated<GameListItem>;
};

defineProps<Props>();

const isReady = useHasPageProp('games');

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Jogos',
                href: gamesIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Jogos" />

    <div
        v-if="isReady"
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading
            variant="default"
            title="Jogos"
            description="Todos os jogos do campeonato, do primeiro ao último"
            class="text-center"
        />

        <GamesList
            v-if="games"
            :games="games"
            scroll-prop="games"
            empty-message="Nenhum jogo cadastrado."
            action-label="Ver jogo"
            show-user-prediction
        />
    </div>
</template>
