import { Link, usePage } from '@inertiajs/react';
import { ReactNode, useEffect, useState } from 'react';
import AlertModal from '@/Components/Alert/AlertModal';
import FlashAlert from '@/Components/FlashAlert';
import CookieConsent from '@/Components/CookieConsent';
import { PageTourButton, PageTourProvider } from '@/Components/PageTour';
import ThemeSync from '@/Components/ThemeSync';
import { Skeleton } from '@/Components/ui/skeleton';
import { usePageNavigationLoading } from '@/hooks/usePageNavigationLoading';
import { Sheet, SheetContent } from '@/Components/ui/sheet';
import AppHeader, { SidebarLogoutButton, SidebarToggleButton } from '@/Layouts/Partials/AppHeader';
import ImpersonationBanner from '@/Layouts/Partials/ImpersonationBanner';
import SidebarNav from '@/Layouts/Partials/SidebarNav';
import { PageProps } from '@/types';

interface AppLayoutProps {
    header?: ReactNode;
    children: ReactNode;
}

export default function AppLayout({ header, children }: AppLayoutProps) {
    const { app, sidebarNavigation, impersonating } = usePage<PageProps>().props;
    const [collapsed, setCollapsed] = useState(
        () => typeof window !== 'undefined' && localStorage.getItem('sidebarCollapsed') === 'true',
    );
    const [mobileOpen, setMobileOpen] = useState(false);
    const { isLoading, isNavigating, SkeletonComponent } = usePageNavigationLoading();

    useEffect(() => {
        localStorage.setItem('sidebarCollapsed', collapsed ? 'true' : 'false');
    }, [collapsed]);

    return (
        <PageTourProvider>
        <div className="flex h-screen overflow-hidden bg-background">
            <aside
                className={`hidden shrink-0 flex-col border-r border-border bg-surface transition-all duration-300 md:flex ${
                    collapsed ? 'w-20' : 'w-64'
                }`}
            >
                <div
                    className={`flex h-20 items-center border-b border-border transition-all duration-300 ${
                        collapsed ? 'justify-center px-0' : 'justify-between px-4'
                    }`}
                >
                    <Link href={route('dashboard')} className={`flex items-center gap-2 overflow-hidden ${collapsed ? 'hidden' : ''}`}>
                        {app.logoUrl ? (
                            <img src={app.logoUrl} alt={app.name} className="h-8 w-auto max-w-[10rem] object-contain" />
                        ) : (
                            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary font-bold text-white">
                                {app.name.charAt(0)}
                            </div>
                        )}
                        <span className="truncate text-lg font-bold tracking-tight">{app.name}</span>
                    </Link>
                    <SidebarToggleButton collapsed={collapsed} onToggle={() => setCollapsed((value) => !value)} />
                </div>

                <nav className="flex-1 overflow-y-auto px-4 py-6">
                    <SidebarNav navigation={sidebarNavigation} collapsed={collapsed} />
                </nav>

                <div className="flex flex-col gap-2 border-t border-border p-4">
                    <SidebarLogoutButton collapsed={collapsed} />
                </div>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                <AppHeader
                    title={isNavigating ? <Skeleton className="h-7 w-48" /> : header}
                    onMobileMenuOpen={() => setMobileOpen(true)}
                    tourButton={<PageTourButton />}
                />
                {impersonating && <ImpersonationBanner />}
                <main className="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8" data-tour="page-content">
                    {isLoading ? <SkeletonComponent /> : children}
                </main>
            </div>

            <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                <SheetContent className="p-0">
                    <div className="flex h-20 items-center justify-between border-b border-border px-6">
                        <span className="text-xl font-bold tracking-tight">{app.name}</span>
                    </div>
                    <nav className="flex-1 overflow-y-auto px-4 py-6">
                        <SidebarNav navigation={sidebarNavigation} />
                    </nav>
                    <div className="border-t border-border p-4">
                        <SidebarLogoutButton collapsed={false} />
                    </div>
                </SheetContent>
            </Sheet>

            <ThemeSync />
            <FlashAlert />
            <AlertModal />
            <CookieConsent />
        </div>
        </PageTourProvider>
    );
}
