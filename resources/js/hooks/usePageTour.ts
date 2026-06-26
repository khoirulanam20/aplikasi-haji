import { usePage } from '@inertiajs/react';
import { driver, type DriveStep } from 'driver.js';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { getTourStepsForRoute } from '@/config/tours';
import { completeTour } from '@/lib/tourApi';
import { PageProps } from '@/types';

const AUTO_START_DELAY_MS = 400;

function getCurrentRouteName(): string | null {
    const current = route().current();

    return typeof current === 'string' ? current : null;
}

function buildDriveSteps(routeName: string): DriveStep[] {
    return getTourStepsForRoute(routeName)
        .filter((step) => document.querySelector(step.element) !== null)
        .map((step) => ({
            element: step.element,
            popover: {
                title: step.title,
                description: step.description,
                side: step.side,
                align: step.align,
            },
        }));
}

export function usePageTour() {
    const { auth, url } = usePage<PageProps>().props;
    const [pendingTours, setPendingTours] = useState<Record<string, boolean>>({});
    const completedTours = useMemo(() => {
        const serverCompletedTours = auth.user?.completed_tours ?? {};

        return { ...serverCompletedTours, ...pendingTours };
    }, [auth.user?.completed_tours, pendingTours]);
    const driverRef = useRef<ReturnType<typeof driver> | null>(null);
    const isRunningRef = useRef(false);

    const routeName = getCurrentRouteName();
    const isCompleted = routeName ? Boolean(completedTours[routeName]) : true;
    const canShowTour = routeName !== null && getTourStepsForRoute(routeName).length > 0;

    const destroyDriver = useCallback(() => {
        if (driverRef.current) {
            driverRef.current.destroy();
            driverRef.current = null;
        }

        isRunningRef.current = false;
    }, []);

    const markTourComplete = useCallback(async (name: string) => {
        if (completedTours[name]) {
            return;
        }

        setPendingTours((prev) => ({ ...prev, [name]: true }));

        try {
            await completeTour(name);
        } catch {
            setPendingTours((prev) => {
                const next = { ...prev };
                delete next[name];
                return next;
            });
        }
    }, [completedTours]);

    const startTour = useCallback(
        (options?: { force?: boolean }) => {
            const name = getCurrentRouteName();

            if (!name || isRunningRef.current) {
                return;
            }

            const steps = buildDriveSteps(name);

            if (steps.length === 0) {
                return;
            }

            if (!options?.force && completedTours[name]) {
                return;
            }

            destroyDriver();

            const shouldPersist = !completedTours[name];
            isRunningRef.current = true;

            const instance = driver({
                showProgress: true,
                progressText: '{{current}} dari {{total}}',
                nextBtnText: 'Lanjut',
                prevBtnText: 'Kembali',
                doneBtnText: 'Selesai',
                popoverClass: 'app-tour-popover',
                steps,
                onDestroyed: () => {
                    isRunningRef.current = false;
                    driverRef.current = null;

                    if (shouldPersist) {
                        void markTourComplete(name);
                    }
                },
            });

            driverRef.current = instance;
            instance.drive();
        },
        [completedTours, destroyDriver, markTourComplete],
    );

    useEffect(() => {
        const name = getCurrentRouteName();

        if (!name || completedTours[name]) {
            return;
        }

        const timer = window.setTimeout(() => {
            startTour();
        }, AUTO_START_DELAY_MS);

        return () => {
            window.clearTimeout(timer);
            destroyDriver();
        };
    }, [url, completedTours, startTour, destroyDriver]);

    useEffect(() => () => destroyDriver(), [destroyDriver]);

    return {
        routeName,
        canShowTour,
        isCompleted,
        startTour,
    };
}
