<script setup lang="ts">
import AppBottomNav from '@/components/AppBottomNav.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppTopNav from '@/components/AppTopNav.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header">
        <AppTopNav :breadcrumbs="breadcrumbs" />
        <AppContent
            variant="header"
            class="overflow-x-hidden pb-24 md:pb-0"
        >
            <slot />
        </AppContent>
        <AppBottomNav />
        <Toaster />
    </AppShell>
</template>
