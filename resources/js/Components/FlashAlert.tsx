import { useEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { useAlert } from '@/Components/Alert/AlertContext';
import { PageProps } from '@/types';

export default function FlashAlert() {
    const { flash, errors } = usePage<PageProps>().props;
    const { alert } = useAlert();
    const lastFlashKey = useRef<string | null>(null);

    useEffect(() => {
        if (!flash || flash.key === lastFlashKey.current) {
            return;
        }

        lastFlashKey.current = flash.key;
        void alert({
            type: flash.type,
            title: flash.title,
            message: flash.message,
        });
    }, [flash, alert]);

    const lastErrorsKey = useRef('');

    useEffect(() => {
        const errorValues = Object.values(errors ?? {}).flat();
        if (errorValues.length === 0) {
            return;
        }

        const key = errorValues.join('|');
        if (key === lastErrorsKey.current) {
            return;
        }

        lastErrorsKey.current = key;
        void alert({
            type: 'error',
            title: 'Gagal',
            message: errorValues.join('\n'),
        });
    }, [errors, alert]);

    return null;
}
