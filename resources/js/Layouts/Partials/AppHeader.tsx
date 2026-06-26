import { Link, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import UserAvatar from '@/Components/UserAvatar';
import { useAnyPermission } from '@/hooks/usePermission';
import { PageProps } from '@/types';

interface AppHeaderProps {
    title?: ReactNode;
    onMobileMenuOpen: () => void;
    tourButton?: ReactNode;
}

export default function AppHeader({ title, onMobileMenuOpen, tourButton }: AppHeaderProps) {
    const { auth } = usePage<PageProps>().props;
    const canViewProfile = useAnyPermission(['profile.read', 'profile.list']);

    return (
        <header className="relative z-10 flex h-20 shrink-0 items-center justify-between border-b border-border bg-surface px-4 sm:px-6 lg:px-8">
            <div className="flex items-center gap-4" data-tour="page-header">
                <button
                    type="button"
                    className="rounded-md p-2 text-text-secondary hover:bg-gray-100 md:hidden"
                    onClick={onMobileMenuOpen}
                >
                    <IconoirIcon name="menu" className="text-2xl" />
                </button>
                {title && (
                    <div className="hidden items-center gap-2 sm:flex">
                        <h1 className="text-2xl font-bold text-text-primary">{title}</h1>
                        {tourButton}
                    </div>
                )}
            </div>

            <div className="flex items-center gap-4">
                <div className="relative hidden md:block">
                    <IconoirIcon
                        name="search"
                        className="absolute left-3 top-1/2 -translate-y-1/2 text-xl text-text-secondary"
                    />
                    <input
                        type="text"
                        placeholder="Search..."
                        className="w-64 rounded-md border border-border bg-gray-50 py-2 pl-10 pr-4 text-sm transition-all focus:w-80 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20"
                    />
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            className="relative flex h-10 w-10 items-center justify-center rounded-full text-text-secondary hover:bg-gray-100"
                        >
                            <IconoirIcon name="bell" className="text-2xl" />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-80">
                        <div className="border-b border-border px-4 py-2">
                            <h3 className="text-sm font-bold">Notifications</h3>
                        </div>
                        <div className="px-4 py-3 text-sm text-text-secondary">
                            You have no new notifications.
                        </div>
                        {canViewProfile && (
                            <div className="border-t border-border px-4 py-2 text-center">
                                <Link
                                    href={route('notifications.index')}
                                    className="text-xs font-medium text-primary hover:underline"
                                >
                                    View all
                                </Link>
                            </div>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type="button" className="flex items-center gap-3 rounded-full p-1 hover:bg-gray-100">
                            <UserAvatar user={auth.user} />
                            <div className="hidden text-left md:block">
                                <p className="mb-1 text-sm font-semibold leading-none text-text-primary">
                                    {auth.user?.name}
                                </p>
                                <p className="text-xs leading-none text-text-secondary">{auth.user?.email}</p>
                            </div>
                            <IconoirIcon name="nav-arrow-down" className="hidden text-lg text-text-secondary md:block" />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-48">
                        <div className="border-b border-border px-4 py-2 md:hidden">
                            <p className="text-sm font-semibold">{auth.user?.name}</p>
                            <p className="truncate text-xs text-text-secondary">{auth.user?.email}</p>
                        </div>
                        {canViewProfile && (
                            <DropdownMenuItem asChild>
                                <Link href={route('profile.edit')} className="flex items-center gap-2">
                                    <IconoirIcon name="user" className="text-base" />
                                    Profile Settings
                                </Link>
                            </DropdownMenuItem>
                        )}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="flex w-full items-center gap-2 text-danger"
                            >
                                <IconoirIcon name="log-out" className="text-base" />
                                Logout
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}

export function SidebarToggleButton({
    collapsed,
    onToggle,
}: {
    collapsed: boolean;
    onToggle: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onToggle}
            className="flex shrink-0 items-center justify-center rounded-md p-1.5 text-text-secondary hover:bg-gray-100"
        >
            <IconoirIcon
                name={collapsed ? 'sidebar-expand' : 'sidebar-collapse'}
                className="text-xl"
            />
        </button>
    );
}

export function SidebarLogoutButton({ collapsed }: { collapsed: boolean }) {
    return (
        <Link
            href={route('logout')}
            method="post"
            as="button"
            className={`flex w-full items-center gap-3 rounded-md p-2 text-text-secondary transition-colors hover:bg-red-50 hover:text-danger ${
                collapsed ? 'justify-center px-0' : ''
            }`}
        >
            <IconoirIcon name="log-out" className="shrink-0 text-xl" />
            {!collapsed && <span className="text-sm font-medium">Logout</span>}
        </Link>
    );
}
