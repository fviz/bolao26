<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: NonNullable<InertiaLinkProps['href']>;
    component: string;
}>();
</script>

<template>
    <Link :href="href" :component="component" prefetch>
        <slot />
    </Link>
</template>
