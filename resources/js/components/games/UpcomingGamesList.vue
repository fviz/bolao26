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
    <div class="flex flex-col gap-4">
        <Heading
            variant="default"
            title="Lista de jogos"
            description="Próximos jogos do campeonato"
            class="text-center"
        />

        <GamesList
            :games="games"
            scroll-prop="games"
            empty-message="Nenhum jogo programado no momento."
            action-label-without-prediction="Faça seu palpite"
            action-label-with-prediction="Veja seu palpite"
            show-user-prediction
        />
    </div>
</template>
