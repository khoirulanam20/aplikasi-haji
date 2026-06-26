import { Link, usePage } from '@inertiajs/react';
import IconoirHtml from '@/Components/IconoirHtml';
import { isSidebarMenuActive } from '@/lib/navActive';
import { cn } from '@/lib/utils';
import { MenuItem } from '@/types';

interface SidebarNavItemProps {
    menu: MenuItem;
    collapsed?: boolean;
}

export default function SidebarNavItem({ menu, collapsed = false }: SidebarNavItemProps) {
    const { url } = usePage();
    const isActive = isSidebarMenuActive(menu, url);

    return (
        <Link
            href={menu.url}
            className={cn(
                'sidebar-nav-item flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors',
                isActive
                    ? 'sidebar-nav-item--active text-text-primary'
                    : 'text-text-secondary hover:bg-gray-100 hover:text-text-primary',
                collapsed && 'justify-center px-2',
            )}
            title={menu.label}
        >
            <span
                className={cn(
                    'sidebar-nav-item__icon flex h-5 w-5 shrink-0 items-center justify-center text-lg',
                    !isActive && 'text-text-secondary',
                )}
            >
                <IconoirHtml html={menu.icon} fallback="•" />
            </span>
            {!collapsed && <span className="truncate">{menu.label}</span>}
        </Link>
    );
}
