<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/FeaturedAchievementController';
import { Button } from '@/components/ui/button';

type Props = {
    userId: number;
    achievementSlug: string;
    isFeatured: boolean;
    earned: boolean;
    locked?: boolean;
    size?: 'default' | 'sm';
    variant?: 'default' | 'outline' | 'secondary';
    setLabel?: string;
    clearLabel?: string;
};

withDefaults(defineProps<Props>(), {
    locked: false,
    size: 'default',
    variant: 'outline',
    setLabel: 'Destacar',
    clearLabel: 'Remover destaque',
});
</script>

<template>
    <Form
        v-if="earned && !locked"
        v-bind="update.form({ user: userId })"
        :options="{ preserveScroll: true }"
    >
        <input
            type="hidden"
            name="achievementSlug"
            :value="isFeatured ? '' : achievementSlug"
        />
        <Button type="submit" :size="size" :variant="variant">
            {{ isFeatured ? clearLabel : setLabel }}
        </Button>
    </Form>
</template>
