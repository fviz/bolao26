<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    BookOpen,
    ClipboardList,
    Trophy,
    Volleyball,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, rules } from '@/routes';
import { index as notificationsIndex } from '@/routes/notifications';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as rankingIndex } from '@/routes/ranking';
import type { NavItem } from '@/types';

const page = usePage();

const unreadNotificationsCount = computed(() => {
    const notifications = page.props.notifications as
        | { unreadCount?: number }
        | undefined;

    return notifications?.unreadCount ?? 0;
});

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Painel do Bolão',
        href: dashboard(),
        icon: Volleyball,
    },
    {
        title: 'Ranking',
        href: rankingIndex(),
        icon: Trophy,
    },
    {
        title: 'Meus palpites',
        href: predictionsIndex(),
        icon: ClipboardList,
    },
    {
        title: 'Notificações',
        href: notificationsIndex(),
        icon: Bell,
        badge: unreadNotificationsCount.value || undefined,
    },
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Regras do Bolão',
        href: rules(),
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
