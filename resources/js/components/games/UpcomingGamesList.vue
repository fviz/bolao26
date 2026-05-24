<script setup lang="ts">
import GamesList from '@/components/games/GamesList.vue';
import Heading from '@/components/Heading.vue';
import type { GameListItem, Paginated } from '@/types/game';

type Props = {
    games: Paginated<GameListItem>;
};

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col gap-4 p-4 md:p-6">
        <Heading
            variant="small"
            title="Lista de jogos"
            description="Próximos jogos do campeonato"
        />

        <GamesList
            :games="games"
            empty-message="Nenhum jogo programado no momento."
        />
    </div>
</template>
