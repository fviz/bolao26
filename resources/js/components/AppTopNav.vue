<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppNavigation } from '@/composables/useAppNavigation';
import { getInitials } from '@/composables/useInitials';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { navItems, isNavItemActive } = useAppNavigation();

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';
</script>

<template>
    <div class="hidden md:block">
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <Link :href="dashboard()" class="flex shrink-0 items-center gap-x-2">
                    <AppLogo />
                </Link>

                <nav
                    class="ml-8 flex h-full flex-1 items-stretch gap-1"
                    aria-label="Navegação principal"
                >
                    <div
                        v-for="item in navItems"
                        :key="item.title"
                        class="relative flex h-full items-center"
                    >
                        <Link
                            :href="item.href"
                            prefetch
                            :class="[
                                'flex h-9 cursor-pointer items-center rounded-md px-3 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                                isNavItemActive(item) ? activeItemStyles : '',
                            ]"
                        >
                            <component
                                v-if="item.icon"
                                :is="item.icon"
                                class="mr-2 size-4"
                            />
                            {{ item.title }}
                            <span
                                v-if="item.badge"
                                class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                        <div
                            v-if="isNavItemActive(item)"
                            class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                        />
                    </div>
                </nav>

                <div class="ml-auto flex items-center">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
