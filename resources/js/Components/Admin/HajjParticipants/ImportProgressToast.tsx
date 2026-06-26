import IconoirIcon from '@/Components/IconoirIcon';
import { cn } from '@/lib/utils';

interface ImportProgressToastProps {
    title: string;
    description: string;
    processed?: number;
    total?: number;
    variant?: 'default' | 'error';
}

export default function ImportProgressToast({
    title,
    description,
    processed = 0,
    total = 0,
    variant = 'default',
}: ImportProgressToastProps) {
    const hasProgress = total > 0 && variant === 'default';
    const percent = hasProgress ? Math.min(100, Math.round((processed / total) * 100)) : null;

    return (
        <div
            className={cn(
                'fixed bottom-4 right-4 z-40 w-80 rounded-lg border bg-white p-4 shadow-lg',
                variant === 'error' ? 'border-danger/40' : 'border-border',
            )}
            role="status"
            aria-live="polite"
        >
            <div className="flex gap-3">
                <div
                    className={cn(
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                        variant === 'error' ? 'bg-danger/10' : 'bg-primary/10',
                    )}
                >
                    <IconoirIcon
                        name={variant === 'error' ? 'info-circle' : 'refresh-double'}
                        className={cn(
                            'text-xl',
                            variant === 'error' ? 'text-danger' : 'animate-spin text-primary',
                        )}
                    />
                </div>
                <div className="min-w-0 flex-1 space-y-2">
                    <div className="space-y-1">
                        <p className="font-medium leading-tight">{title}</p>
                        <p className="text-sm text-muted-foreground">{description}</p>
                    </div>
                    {hasProgress && (
                        <div className="space-y-1.5">
                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-primary transition-all duration-300"
                                    style={{ width: `${percent}%` }}
                                />
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {processed} / {total} peserta ({percent}%)
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
