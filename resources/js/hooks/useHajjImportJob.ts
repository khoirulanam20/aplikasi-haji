import { useEffect, useRef } from 'react';
import { HajjImportJobState, HajjImportSummary } from '@/types';

interface UseHajjImportJobOptions {
    token: string | null;
    onCompleted: (summary: HajjImportSummary) => void;
    onFailed: (message: string) => void;
    onProgress?: (state: HajjImportJobState) => void;
}

export function useHajjImportJob({ token, onCompleted, onFailed, onProgress }: UseHajjImportJobOptions): void {
    const callbacksRef = useRef({ onCompleted, onFailed, onProgress });
    callbacksRef.current = { onCompleted, onFailed, onProgress };

    useEffect(() => {
        if (!token) {
            return;
        }

        let cancelled = false;
        let intervalId: ReturnType<typeof setInterval> | null = null;

        const poll = async () => {
            try {
                const response = await fetch(route('app.hajj-participants.import.status', token), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (cancelled) {
                    return;
                }

                if (response.status === 404) {
                    onFailed('Status import tidak ditemukan.');
                    if (intervalId) clearInterval(intervalId);
                    return;
                }

                if (!response.ok) {
                    onFailed('Gagal memeriksa status import.');
                    if (intervalId) clearInterval(intervalId);
                    return;
                }

                const data = (await response.json()) as HajjImportJobState;
                callbacksRef.current.onProgress?.(data);

                if (data.status === 'completed' && data.summary) {
                    callbacksRef.current.onCompleted(data.summary);
                    if (intervalId) clearInterval(intervalId);
                    return;
                }

                if (data.status === 'failed') {
                    callbacksRef.current.onFailed(data.message ?? 'Import gagal.');
                    if (intervalId) clearInterval(intervalId);
                }
            } catch {
                if (!cancelled) {
                    callbacksRef.current.onFailed('Gagal memeriksa status import.');
                    if (intervalId) clearInterval(intervalId);
                }
            }
        };

        void poll();
        intervalId = setInterval(poll, 1500);

        return () => {
            cancelled = true;
            if (intervalId) clearInterval(intervalId);
        };
    }, [token, onFailed]);
}
