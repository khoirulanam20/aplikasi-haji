import { router } from '@inertiajs/react';
import { ComponentType, useEffect, useState } from 'react';
import DefaultPageSkeleton from '@/Components/Loading/DefaultPageSkeleton';
import { pathnameFromVisitUrl, resolvePageSkeleton } from '@/lib/pageSkeletonRegistry';

function shouldShowSkeleton(method: string, preserveState: unknown): boolean {
    if (method.toLowerCase() !== 'get') {
        return false;
    }

    return preserveState !== true && preserveState !== 'errors';
}

function resolveCurrentPageSkeleton(): ComponentType {
    if (typeof window === 'undefined') {
        return DefaultPageSkeleton;
    }

    return resolvePageSkeleton(window.location.pathname);
}

export function usePageNavigationLoading(): {
    isNavigating: boolean;
    isInitialLoading: boolean;
    isLoading: boolean;
    SkeletonComponent: ComponentType;
} {
    const [isInitialLoading, setIsInitialLoading] = useState(
        () => typeof window !== 'undefined' && document.readyState !== 'complete',
    );
    const [isNavigating, setIsNavigating] = useState(false);
    const [SkeletonComponent, setSkeletonComponent] = useState<ComponentType>(resolveCurrentPageSkeleton);

    useEffect(() => {
        if (document.readyState === 'complete') {
            return;
        }

        const finishInitialLoad = () => setIsInitialLoading(false);
        window.addEventListener('load', finishInitialLoad, { once: true });

        return () => window.removeEventListener('load', finishInitialLoad);
    }, []);

    useEffect(() => {
        const removeStart = router.on('start', (event) => {
            const visit = event.detail.visit;

            if (!shouldShowSkeleton(visit.method, visit.preserveState)) {
                return;
            }

            const url = typeof visit.url === 'string' ? visit.url : visit.url.href;
            const pathname = pathnameFromVisitUrl(url);
            setSkeletonComponent(() => resolvePageSkeleton(pathname));
            setIsNavigating(true);
        });

        const removeFinish = router.on('finish', () => {
            setIsNavigating(false);
        });

        const removeCancel = router.on('cancel', () => {
            setIsNavigating(false);
        });

        return () => {
            removeStart();
            removeFinish();
            removeCancel();
        };
    }, []);

    return {
        isNavigating,
        isInitialLoading,
        isLoading: isInitialLoading || isNavigating,
        SkeletonComponent,
    };
}
