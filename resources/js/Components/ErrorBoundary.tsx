import { Component, ErrorInfo, ReactNode } from 'react';
import { Button } from '@/Components/ui/button';

interface ErrorBoundaryProps {
    children: ReactNode;
}

interface ErrorBoundaryState {
    hasError: boolean;
}

export default class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
    state: ErrorBoundaryState = { hasError: false };

    static getDerivedStateFromError(): ErrorBoundaryState {
        return { hasError: true };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        console.error('UI error boundary:', error, info);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="flex min-h-[40vh] flex-col items-center justify-center gap-4 p-8 text-center">
                    <h2 className="text-xl font-semibold text-text-primary">Terjadi kesalahan pada tampilan</h2>
                    <p className="max-w-md text-sm text-text-secondary">
                        Muat ulang halaman. Jika masalah berlanjut, hubungi administrator.
                    </p>
                    <Button type="button" onClick={() => window.location.reload()}>
                        Muat ulang
                    </Button>
                </div>
            );
        }

        return this.props.children;
    }
}
