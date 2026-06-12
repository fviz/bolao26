<script setup lang="ts">
import AppNavLink from '@/components/AppNavLink.vue';
import { useAppNavigation } from '@/composables/useAppNavigation';
import { cn } from '@/lib/utils';

const { navItems, isNavItemActive } = useAppNavigation();
</script>

<template>
    <nav
        class="fixed inset-x-4 bottom-4 z-50 md:hidden"
        aria-label="Navegação principal"
    >
        <div
            class="mx-auto flex max-w-lg items-center justify-around rounded-full border border-border/60 bg-background/50 px-2 py-2 shadow-lg backdrop-blur-lg pb-[calc(0.5rem+env(safe-area-inset-bottom))]"
        >
            <AppNavLink
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                :component="item.component"
                :class="
                    cn(
                        'relative flex min-w-0 flex-1 flex-col items-center gap-0.5 rounded-full px-1 py-1.5 text-[10px] font-medium transition-colors',
                        isNavItemActive(item)
                            ? 'text-primary'
                            : 'text-muted-foreground hover:text-foreground',
                    )
                "
            >
                <span class="relative">
                    <component
                        :is="item.icon"
                        :class="
                            cn(
                                'size-5',
                                isNavItemActive(item) && 'stroke-[2.5]',
                            )
                        "
                    />
                    <span
                        v-if="item.badge"
                        class="absolute -top-1 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-semibold text-primary-foreground"
                    >
                        {{ item.badge }}
                    </span>
                </span>
                <span class="truncate">{{ item.title }}</span>
            </AppNavLink>
        </div>
    </nav>
</template>
