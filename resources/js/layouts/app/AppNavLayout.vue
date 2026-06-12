<script setup lang="ts">
import AppBottomNav from '@/components/AppBottomNav.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppTopNav from '@/components/AppTopNav.vue';
import { Spinner } from '@/components/ui/spinner';
import { Toaster } from '@/components/ui/sonner';
import { useInertiaVisitLoading } from '@/composables/useInertiaVisitLoading';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { isNavigating } = useInertiaVisitLoading();
</script>

<template>
    <AppShell variant="header">
        <AppTopNav :breadcrumbs="breadcrumbs" />
        <div class="relative flex flex-1 flex-col">
            <AppContent
                variant="header"
                class="overflow-x-hidden pb-24 md:pb-0"
                :aria-busy="isNavigating"
            >
                <slot />
            </AppContent>
            <div
                v-if="isNavigating"
                class="absolute inset-0 z-40 flex items-center justify-center bg-background/60 backdrop-blur-[1px]"
                aria-hidden="true"
            >
                <Spinner class="size-8 text-muted-foreground" />
            </div>
        </div>
        <AppBottomNav />
        <Toaster />
    </AppShell>
</template>
