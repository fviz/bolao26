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
            icon: Volleyball,
        },
        {
            title: 'Ranking',
            href: rankingIndex(),
            icon: Trophy,
        },
        {
            title: 'Palpites',
            href: predictionsIndex(),
            icon: ClipboardList,
        },
        {
            title: 'Notificações',
            href: notificationsIndex(),
            icon: Bell,
            badge: unreadNotificationsCount.value || undefined,
        },
        {
            title: 'Perfil',
            href: editProfile(),
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
