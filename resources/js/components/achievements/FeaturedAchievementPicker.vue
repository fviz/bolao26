<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/FeaturedAchievementController';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Achievement } from '@/types/achievement';

type Props = {
    userId: number;
    earnedAchievements: Achievement[];
};

defineProps<Props>();

const open = ref(false);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                Escolher medalha
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Medalha em destaque</DialogTitle>
                <DialogDescription>
                    Escolha uma medalha conquistada para exibir ao lado do seu
                    nome.
                </DialogDescription>
            </DialogHeader>

            <div
                v-if="earnedAchievements.length"
                class="grid max-h-80 grid-cols-3 gap-3 overflow-y-auto py-2"
            >
                <Form
                    v-for="achievement in earnedAchievements"
                    :key="achievement.slug"
                    v-bind="update.form({ user: userId })"
                    :options="{ preserveScroll: true }"
                    @success="open = false"
                >
                    <input
                        type="hidden"
                        name="achievementSlug"
                        :value="achievement.slug"
                    />
                    <button
                        type="submit"
                        class="flex w-full flex-col items-center gap-2 rounded-lg p-2 text-center transition-colors hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :class="{
                            'ring-2 ring-primary ring-offset-2 ring-offset-background':
                                achievement.isFeatured,
                        }"
                    >
                        <AchievementEmblem
                            :achievement="achievement"
                            size="sm"
                        />
                        <span class="line-clamp-2 text-xs text-muted-foreground">
                            {{ achievement.name }}
                        </span>
                        <span
                            v-if="achievement.isFeatured"
                            class="text-[10px] font-medium uppercase tracking-wide text-primary"
                        >
                            Em destaque
                        </span>
                    </button>
                </Form>
            </div>
            <p v-else class="text-sm text-muted-foreground">
                Você ainda não conquistou nenhuma medalha.
            </p>

            <Form
                v-bind="update.form({ user: userId })"
                :options="{ preserveScroll: true }"
                @success="open = false"
            >
                <input type="hidden" name="achievementSlug" value="" />
                <Button type="submit" variant="ghost" size="sm" class="w-full">
                    Remover medalha em destaque
                </Button>
            </Form>
        </DialogContent>
    </Dialog>
</template>
