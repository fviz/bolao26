<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy } from '@/actions/App/Http/Controllers/GameCommentController';
import GameCommentAvatar from '@/components/games/GameCommentAvatar.vue';
import GameCommentForm from '@/components/games/GameCommentForm.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserProfileLink from '@/components/users/UserProfileLink.vue';
import type { GameComment } from '@/types/game';

type Props = {
    gameId: number;
    comment: GameComment;
    canReply?: boolean;
};

withDefaults(defineProps<Props>(), {
    canReply: true,
});

const showReplyForm = ref(false);

const formattedCreatedAt = (iso: string): string => {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(iso));
};
</script>

<template>
    <li class="space-y-3">
        <div class="flex items-start gap-3">
            <GameCommentAvatar
                :user-name="comment.userName"
                :user-avatar="comment.userAvatar"
            />
            <div class="min-w-0 flex-1 space-y-1">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium">
                        <UserProfileLink
                            :user-id="comment.userId"
                            :user-name="comment.userName"
                            :is-current-user="comment.isCurrentUser"
                            :featured-achievement="comment.featuredAchievement"
                        />
                    </p>
                    <DropdownMenu v-if="canReply || comment.isCurrentUser">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8 shrink-0"
                                :aria-label="'Opções do comentário'"
                            >
                                <MoreHorizontal class="size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem
                                v-if="canReply"
                                @select="
                                    (event) => {
                                        event.preventDefault();
                                        showReplyForm = !showReplyForm;
                                    }
                                "
                            >
                                Responder
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                v-if="comment.isCurrentUser"
                                variant="destructive"
                                as-child
                            >
                                <Form
                                    v-bind="
                                        destroy.form({
                                            game: gameId,
                                            comment: comment.id,
                                        })
                                    "
                                    :options="{ preserveScroll: true }"
                                    class="w-full"
                                >
                                    <button
                                        type="submit"
                                        class="w-full cursor-default text-left"
                                    >
                                        Excluir
                                    </button>
                                </Form>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <p class="text-muted-foreground text-xs">
                    {{ formattedCreatedAt(comment.createdAt) }}
                </p>
                <p class="text-sm whitespace-pre-wrap break-words">
                    {{ comment.body }}
                </p>
            </div>
        </div>

        <div
            v-if="showReplyForm && canReply"
            class="ml-2 border-l border-border pl-4"
        >
            <GameCommentForm
                :game-id="gameId"
                :parent-id="comment.id"
                label="Sua resposta"
                placeholder="Escreva sua resposta…"
                submit-label="Responder"
                auto-focus
                @success="showReplyForm = false"
            />
        </div>

        <ul
            v-if="comment.replies.length"
            class="ml-2 space-y-3 border-l border-border pl-4"
        >
            <GameCommentItem
                v-for="reply in comment.replies"
                :key="reply.id"
                :game-id="gameId"
                :comment="reply"
                :can-reply="false"
            />
        </ul>
    </li>
</template>
