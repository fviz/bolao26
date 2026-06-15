<script setup lang="ts">
import { InfiniteScroll } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type Props = {
    data: string;
};

defineProps<Props>();

defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <div v-bind="$attrs">
        <InfiniteScroll :data="data" manual only-next preserve-url>
            <slot />

            <template #next="{ loading, fetch, hasMore }">
                <div v-if="hasMore" class="flex justify-center py-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="min-h-10"
                        :disabled="loading"
                        @click="fetch"
                    >
                        {{ loading ? 'Carregando...' : 'Carregar mais' }}
                    </Button>
                </div>
            </template>
        </InfiniteScroll>
    </div>
</template>
