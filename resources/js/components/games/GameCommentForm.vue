<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { store } from '@/actions/App/Http/Controllers/GameCommentController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    gameId: number;
    parentId?: number;
    label?: string;
    placeholder?: string;
    submitLabel?: string;
    autoFocus?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    label: 'Novo comentário',
    placeholder: 'Escreva seu comentário…',
    submitLabel: 'Publicar',
});

const emit = defineEmits<{
    success: [];
}>();

const body = ref('');
const charCount = computed(() => body.value.length);
const canSubmit = computed(
    () => body.value.trim().length > 0 && charCount.value <= 250,
);
</script>

<template>
    <Form
        v-bind="store.form(props.gameId)"
        reset-on-success
        :options="{ preserveScroll: true }"
        class="space-y-3"
        @success="emit('success')"
        v-slot="{ errors, processing }"
    >
        <input
            v-if="parentId"
            type="hidden"
            name="parent_id"
            :value="parentId"
        />
        <div class="grid gap-2">
            <div class="flex items-center justify-between gap-2">
                <Label :for="`comment_body_${parentId ?? 'top'}`">{{
                    label
                }}</Label>
                <span
                    class="text-muted-foreground text-xs tabular-nums"
                    :class="{
                        'text-destructive': charCount > 250,
                    }"
                >
                    {{ charCount }}/250
                </span>
            </div>
            <Textarea
                :id="`comment_body_${parentId ?? 'top'}`"
                v-model="body"
                name="body"
                :placeholder="placeholder"
                maxlength="250"
                rows="3"
                :autofocus="autoFocus"
                required
            />
            <InputError :message="errors.body" />
            <InputError :message="errors.parent_id" />
        </div>
        <Button
            type="submit"
            size="sm"
            :disabled="processing || !canSubmit"
        >
            {{ submitLabel }}
        </Button>
    </Form>
</template>
