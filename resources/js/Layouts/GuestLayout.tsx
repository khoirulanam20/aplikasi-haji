import { Link, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';
import AlertModal from '@/Components/Alert/AlertModal';
import FlashAlert from '@/Components/FlashAlert';
import CookieConsent from '@/Components/CookieConsent';
import ThemeSync from '@/Components/ThemeSync';
import { usePageNavigationLoading } from '@/hooks/usePageNavigationLoading';
import { PageProps } from '@/types';

interface GuestLayoutProps {
    children: ReactNode;
}

export default function GuestLayout({ children }: GuestLayoutProps) {
    const { app } = usePage<PageProps>().props;
    const { isLoading, SkeletonComponent } = usePageNavigationLoading();

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-background p-4">
            <Link href={route('login')} className="mb-8 flex items-center gap-3">
                {app.logoUrl ? (
                    <img src={app.logoUrl} alt={app.name} className="h-10 w-auto max-w-[12rem] object-contain" />
                ) : (
                    <div className="flex h-10 w-10 items-center justify-center rounded-md bg-primary text-xl font-bold text-white">
                        {app.name.charAt(0)}
                    </div>
                )}
                <span className="text-2xl font-bold tracking-tight">{app.name}</span>
            </Link>

            <main className="w-full max-w-md">
                <div className="card w-full">{isLoading ? <SkeletonComponent /> : children}</div>
            </main>

            <ThemeSync />
            <FlashAlert />
            <AlertModal />
            <CookieConsent />
        </div>
    );
}
