import { ComponentType } from 'react';
import AuthPageSkeleton from '@/Components/Loading/AuthPageSkeleton';
import DashboardPageSkeleton from '@/Components/Loading/DashboardPageSkeleton';
import DefaultPageSkeleton from '@/Components/Loading/DefaultPageSkeleton';
import FormPageSkeleton from '@/Components/Loading/FormPageSkeleton';
import ModulesPageSkeleton from '@/Components/Loading/ModulesPageSkeleton';
import TablePageSkeleton from '@/Components/Loading/TablePageSkeleton';

type PageSkeletonComponent = ComponentType;

const ROUTE_SKELETONS: Array<{ pattern: RegExp; component: PageSkeletonComponent }> = [
    { pattern: /\/app\/(users|master-data|role-permission|notifications)(\/|$)/, component: TablePageSkeleton },
    { pattern: /\/(app\/(web-setting|config)|profile)(\/|$)/, component: FormPageSkeleton },
    { pattern: /\/app\/dashboard(\/|$)/, component: DashboardPageSkeleton },
    { pattern: /\/app\/modules(\/|$)/, component: ModulesPageSkeleton },
    { pattern: /\/(login|register|forgot-password|reset-password|verify-email|confirm-password)(\/|$)/, component: AuthPageSkeleton },
];

export function resolvePageSkeleton(pathname: string): PageSkeletonComponent {
    const normalized = pathname.replace(/\/+$/, '') || '/';

    for (const entry of ROUTE_SKELETONS) {
        if (entry.pattern.test(normalized)) {
            return entry.component;
        }
    }

    return DefaultPageSkeleton;
}

export function pathnameFromVisitUrl(url: string): string {
    try {
        return new URL(url, window.location.origin).pathname;
    } catch {
        return url.split('?')[0] ?? '/';
    }
}
