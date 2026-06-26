import { createContext, ReactNode, useContext } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import { usePageTour } from '@/hooks/usePageTour';

type PageTourContextValue = ReturnType<typeof usePageTour>;

const PageTourContext = createContext<PageTourContextValue | null>(null);

export function PageTourProvider({ children }: { children: ReactNode }) {
    const tour = usePageTour();

    return <PageTourContext.Provider value={tour}>{children}</PageTourContext.Provider>;
}

export function usePageTourContext(): PageTourContextValue {
    const context = useContext(PageTourContext);

    if (!context) {
        throw new Error('usePageTourContext must be used within PageTourProvider');
    }

    return context;
}

export function PageTourButton() {
    const { canShowTour, startTour } = usePageTourContext();

    if (!canShowTour) {
        return null;
    }

    return (
        <button
            type="button"
            className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-text-secondary transition-colors hover:bg-gray-100 hover:text-text-primary"
            onClick={() => startTour({ force: true })}
            aria-label="Lihat panduan"
            title="Lihat panduan"
        >
            <IconoirIcon name="help-circle" className="text-xl" />
        </button>
    );
}
