<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { logout, rules } from '@/routes';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editNotifications } from '@/routes/notifications/settings';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

type SettingsNavItem = NavItem & {
    matches?: (pathname: string) => boolean;
};

const sidebarNavItems: SettingsNavItem[] = [
    {
        title: 'Perfil',
        href: editProfile(),
    },
    {
        title: 'Segurança',
        href: editSecurity(),
    },
    {
        title: 'Aparência',
        href: editAppearance(),
    },
    {
        title: 'Notificações',
        href: editNotifications(),
    },
    {
        title: 'Regras',
        href: rules(),
        matches: (pathname: string) => pathname === '/rules',
    },
];

const { isCurrentOrParentUrl, currentUrl } = useCurrentUrl();

function isSettingsNavItemActive(item: SettingsNavItem): boolean {
    if (item.matches) {
        return item.matches(currentUrl.value);
    }

    return isCurrentOrParentUrl(item.href);
}

const handleLogout = (): void => {
    router.flushAll();
};
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Ajustes"
            description="Faça ajustes em sua conta e perfil"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Ajustes"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isSettingsNavItemActive(item) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>

                <div class="mt-4 md:hidden">
                    <Button
                        variant="ghost"
                        class="w-full justify-start text-muted-foreground"
                        as-child
                    >
                        <Link
                            :href="logout()"
                            data-test="settings-logout-button"
                            @click="handleLogout"
                            as="button"
                        >
                            <LogOut class="h-4 w-4" />
                            Sair
                        </Link>
                    </Button>
                </div>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
