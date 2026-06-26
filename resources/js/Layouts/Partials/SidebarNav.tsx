import { SidebarNavigation } from '@/types';
import SidebarNavItem from '@/Layouts/Partials/SidebarNavItem';

interface SidebarNavProps {
    navigation: SidebarNavigation;
    collapsed?: boolean;
}

export default function SidebarNav({ navigation, collapsed = false }: SidebarNavProps) {
    return (
        <div>
            {navigation.groups.map((group, groupIndex) => (
                <div key={group.name} className={groupIndex > 0 ? 'mt-4' : ''}>
                    {!collapsed && (
                        <p className="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-text-secondary">
                            {group.name}
                        </p>
                    )}
                    <div className="space-y-0.5">
                        {group.menus.map((menu) => (
                            <SidebarNavItem key={`${menu.route_name}-${menu.label}`} menu={menu} collapsed={collapsed} />
                        ))}
                    </div>
                </div>
            ))}

            {navigation.ungrouped.length > 0 && (
                <div className={navigation.groups.length > 0 ? 'mt-4' : ''}>
                    {navigation.groups.length > 0 && !collapsed && (
                        <p className="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-text-secondary">
                            Lainnya
                        </p>
                    )}
                    <div className="space-y-0.5">
                        {navigation.ungrouped.map((menu) => (
                            <SidebarNavItem key={`${menu.route_name}-${menu.label}`} menu={menu} collapsed={collapsed} />
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
