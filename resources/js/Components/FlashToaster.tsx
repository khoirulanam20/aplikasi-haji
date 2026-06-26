import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { PageProps } from '@/types';

export default function FlashToaster() {
    const { flash } = usePage<PageProps>().props;

    useEffect(() => {
        if (!flash) {
            return;
        }

        const fn =
            flash.type === 'success'
                ? toast.success
                : flash.type === 'warning'
                  ? toast.warning
                  : flash.type === 'error'
                    ? toast.error
                    : toast.info;

        fn(flash.title, { description: flash.message });
    }, [flash]);

    return null;
}
