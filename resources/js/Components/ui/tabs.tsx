import { createContext, ReactNode, useContext, useState } from 'react';
import { cn } from '@/lib/utils';

interface TabsContextValue {
    value: string;
    setValue: (value: string) => void;
}

const TabsContext = createContext<TabsContextValue | null>(null);

function useTabsContext() {
    const context = useContext(TabsContext);

    if (!context) {
        throw new Error('Tabs components must be used within Tabs');
    }

    return context;
}

interface TabsProps {
    defaultValue: string;
    children: ReactNode;
    className?: string;
}

function Tabs({ defaultValue, children, className }: TabsProps) {
    const [value, setValue] = useState(defaultValue);

    return (
        <TabsContext.Provider value={{ value, setValue }}>
            <div className={className}>{children}</div>
        </TabsContext.Provider>
    );
}

interface TabsListProps {
    children: ReactNode;
    className?: string;
}

function TabsList({ children, className }: TabsListProps) {
    return (
        <div
            role="tablist"
            className={cn('flex flex-wrap gap-1 border-b border-border', className)}
        >
            {children}
        </div>
    );
}

interface TabsTriggerProps {
    value: string;
    children: ReactNode;
    className?: string;
}

function TabsTrigger({ value, children, className }: TabsTriggerProps) {
    const { value: activeValue, setValue } = useTabsContext();
    const isActive = activeValue === value;

    return (
        <button
            type="button"
            role="tab"
            aria-selected={isActive}
            className={cn(
                '-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors',
                isActive
                    ? 'border-primary text-primary'
                    : 'border-transparent text-text-secondary hover:text-text-primary',
                className,
            )}
            onClick={() => setValue(value)}
        >
            {children}
        </button>
    );
}

interface TabsContentProps {
    value: string;
    children: ReactNode;
    className?: string;
}

function TabsContent({ value, children, className }: TabsContentProps) {
    const { value: activeValue } = useTabsContext();

    if (activeValue !== value) {
        return null;
    }

    return (
        <div role="tabpanel" className={cn('pt-6', className)}>
            {children}
        </div>
    );
}

export { Tabs, TabsContent, TabsList, TabsTrigger };
