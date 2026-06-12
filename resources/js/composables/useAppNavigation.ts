import { usePage } from '@inertiajs/vue3';
import {
    Bell,
    ClipboardList,
    Trophy,
    User,
    Volleyball,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import { computed, type ComputedRef } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as notificationsIndex } from '@/routes/notifications';
import { edit as editProfile } from '@/routes/profile';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as rankingIndex } from '@/routes/ranking';
import type { NavItem } from '@/types';

export type AppNavItem = NavItem & {
    component: string;
    matches?: (pathname: string) => boolean;
};

type UseAppNavigationReturn = {
    navItems: ComputedRef<AppNavItem[]>;
    isNavItemActive: (item: AppNavItem) => boolean;
};

export function useAppNavigation(): UseAppNavigationReturn {
    const page = usePage();
    const { isCurrentUrl, currentUrl } = useCurrentUrl();

    const unreadNotificationsCount = computed(() => {
        const notifications = page.props.notifications as
            | { unreadCount?: number }
            | undefined;

        return notifications?.unreadCount ?? 0;
    });

    const navItems = computed<AppNavItem[]>(() => [
        {
            title: 'Painel',
            href: dashboard(),
            component: 'Dashboard',
            icon: Volleyball,
        },
        {
            title: 'Ranking',
            href: rankingIndex(),
            component: 'ranking/Index',
            icon: Trophy,
        },
        {
            title: 'Palpites',
            href: predictionsIndex(),
            component: 'predictions/Index',
            icon: ClipboardList,
        },
        {
            title: 'Notificações',
            href: notificationsIndex(),
            component: 'Notifications',
            icon: Bell,
            badge: unreadNotificationsCount.value || undefined,
        },
        {
            title: 'Perfil',
            href: editProfile(),
            component: 'settings/Profile',
            icon: User,
            matches: (pathname: string) =>
                pathname.startsWith('/settings') || pathname === '/rules',
        },
    ]);

    function isNavItemActive(item: AppNavItem): boolean {
        if (item.matches) {
            return item.matches(currentUrl.value);
        }

        return isCurrentUrl(item.href);
    }

    return {
        navItems,
        isNavItemActive,
    };
}

export type { LucideIcon };
