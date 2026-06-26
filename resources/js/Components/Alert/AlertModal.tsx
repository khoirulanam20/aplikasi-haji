import { useEffect } from 'react';
import { Button } from '@/Components/ui/button';
import { getAlertIconClasses, useAlert } from '@/Components/Alert/AlertContext';
import { cn } from '@/lib/utils';

export default function AlertModal() {
    const { state, accept, dismiss } = useAlert();
    const { icon, wrap } = getAlertIconClasses(state.type);

    useEffect(() => {
        if (!state.open) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                dismiss();
            }
        };

        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [state.open, dismiss]);

    if (!state.open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
            <div
                className="fixed inset-0 bg-gray-900/60 backdrop-blur-[2px]"
                onClick={() => (state.mode === 'alert' ? accept() : dismiss())}
            />
            <div className="pointer-events-none fixed inset-0 z-[101] flex min-h-full items-center justify-center p-4 sm:p-6">
                <div className="pointer-events-auto relative w-full max-w-md" onClick={(e) => e.stopPropagation()}>
                    <div className="card overflow-hidden p-6 shadow-xl ring-1 ring-black/5">
                        <div className="flex items-start gap-4">
                            <div
                                className={cn(
                                    'flex h-12 w-12 shrink-0 items-center justify-center rounded-full border',
                                    wrap,
                                )}
                            >
                                <i className={cn('text-2xl', icon)} />
                            </div>
                            <div className="min-w-0 flex-1">
                                <h3 className="text-lg font-bold text-text-primary">{state.title}</h3>
                                <p className="mt-2 whitespace-pre-line text-sm text-text-secondary">
                                    {state.message}
                                </p>
                            </div>
                        </div>
                        <div className="mt-6 flex justify-end gap-3 border-t border-border pt-4">
                            {state.mode === 'confirm' && (
                                <Button type="button" variant="secondary" onClick={dismiss}>
                                    {state.cancelLabel}
                                </Button>
                            )}
                            <Button
                                type="button"
                                onClick={accept}
                                className={
                                    state.type === 'danger' || state.type === 'error'
                                        ? 'bg-danger hover:bg-red-600'
                                        : ''
                                }
                            >
                                {state.confirmLabel}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
