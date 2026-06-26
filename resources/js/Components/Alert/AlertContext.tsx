import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';

export type AlertType = 'success' | 'error' | 'warning' | 'danger' | 'info';
export type AlertMode = 'alert' | 'confirm';

export interface AlertOptions {
    type?: AlertType;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
}

interface AlertState extends Required<Pick<AlertOptions, 'title' | 'message'>> {
    open: boolean;
    mode: AlertMode;
    type: AlertType;
    confirmLabel: string;
    cancelLabel: string;
}

interface AlertContextValue {
    state: AlertState;
    alert: (options?: AlertOptions | string) => Promise<boolean>;
    confirm: (options?: AlertOptions | string) => Promise<boolean>;
    accept: () => void;
    dismiss: () => void;
}

const initialState: AlertState = {
    open: false,
    mode: 'alert',
    type: 'info',
    title: '',
    message: '',
    confirmLabel: 'OK',
    cancelLabel: 'Batal',
};

const AlertContext = createContext<AlertContextValue | null>(null);

function normalizeOptions(options?: AlertOptions | string): AlertOptions {
    if (typeof options === 'string') {
        return { message: options };
    }

    return options ?? {};
}

export function AlertProvider({ children }: { children: ReactNode }) {
    const [state, setState] = useState<AlertState>(initialState);
    const resolverRef = useRef<((value: boolean) => void) | null>(null);

    const close = useCallback((result: boolean) => {
        const resolve = resolverRef.current;
        resolverRef.current = null;
        setState((prev) => ({ ...prev, open: false }));
        document.body.classList.remove('overflow-hidden');
        resolve?.(result);
    }, []);

    const openDialog = useCallback(
        (options: AlertOptions, mode: AlertMode): Promise<boolean> => {
            const normalized = normalizeOptions(options);

            return new Promise((resolve) => {
                resolverRef.current = resolve;
                setState({
                    open: true,
                    mode,
                    type: normalized.type ?? (mode === 'confirm' ? 'warning' : 'info'),
                    title:
                        normalized.title ??
                        (mode === 'confirm' ? 'Konfirmasi' : 'Informasi'),
                    message: normalized.message ?? '',
                    confirmLabel:
                        normalized.confirmLabel ?? (mode === 'confirm' ? 'Ya' : 'OK'),
                    cancelLabel: normalized.cancelLabel ?? 'Batal',
                });
                document.body.classList.add('overflow-hidden');
            });
        },
        [],
    );

    const alert = useCallback(
        (options?: AlertOptions | string) => openDialog(normalizeOptions(options), 'alert'),
        [openDialog],
    );

    const confirm = useCallback(
        (options?: AlertOptions | string) => openDialog(normalizeOptions(options), 'confirm'),
        [openDialog],
    );

    const accept = useCallback(() => {
        close(state.mode === 'confirm');
    }, [close, state.mode]);

    const dismiss = useCallback(() => {
        close(false);
    }, [close]);

    const value = useMemo(
        () => ({ state, alert, confirm, accept, dismiss }),
        [state, alert, confirm, accept, dismiss],
    );

    return <AlertContext.Provider value={value}>{children}</AlertContext.Provider>;
}

export function useAlert() {
    const context = useContext(AlertContext);

    if (!context) {
        throw new Error('useAlert must be used within AlertProvider');
    }

    return context;
}

export function getAlertIconClasses(type: AlertType): { icon: string; wrap: string } {
    const icons: Record<AlertType, string> = {
        success: 'iconoir-check-circle text-success',
        error: 'iconoir-xmark-circle text-danger',
        warning: 'iconoir-warning-circle text-warning',
        danger: 'iconoir-warning-circle text-danger',
        info: 'iconoir-info-circle text-primary',
    };

    const wraps: Record<AlertType, string> = {
        success: 'bg-green-50 border-green-100',
        error: 'bg-red-50 border-red-100',
        warning: 'bg-amber-50 border-amber-100',
        danger: 'bg-red-50 border-red-100',
        info: 'bg-blue-50 border-blue-100',
    };

    return {
        icon: icons[type] ?? icons.info,
        wrap: wraps[type] ?? wraps.info,
    };
}
