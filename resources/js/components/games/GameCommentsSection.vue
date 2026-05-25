<script setup lang="ts">
import { ref } from 'vue';
import GameCommentForm from '@/components/games/GameCommentForm.vue';
import GameCommentItem from '@/components/games/GameCommentItem.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import type { GameComment } from '@/types/game';

type Props = {
    gameId: number;
    comments: GameComment[];
};

defineProps<Props>();

const showNewCommentForm = ref(false);

</script>

<template>
    <section class="rounded-xl border p-4 md:p-6">
        <Heading
            variant="small"
            title="Comentários"
            description="Comente sobre este jogo (máximo 250 caracteres)."
        />

        <div class="mt-4 space-y-6">
            <Button
                v-if="!showNewCommentForm"
                type="button"
                variant="outline"
                @click="showNewCommentForm = true"
            >
                ✏️ Faça um comentário
            </Button>
            <GameCommentForm
                v-else
                :game-id="gameId"
                auto-focus
                @success="showNewCommentForm = false"
            />

            <ol v-if="comments.length" class="space-y-4">
                <GameCommentItem
                    v-for="comment in comments"
                    :key="comment.id"
                    :game-id="gameId"
                    :comment="comment"
                />
            </ol>

            <p v-else class="text-muted-foreground text-sm">
                Nenhum comentário ainda.
            </p>
        </div>
    </section>
</template>
