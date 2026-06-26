import { FormEvent, ReactNode, useRef } from 'react';
import { useAlert } from '@/Components/Alert/AlertContext';
import type { AlertType } from '@/Components/Alert/AlertContext';

interface ConfirmFormProps {
    children: ReactNode;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    type?: AlertType;
    onConfirm?: () => void;
    className?: string;
}

export default function ConfirmForm({
    children,
    title = 'Konfirmasi',
    message = 'Yakin ingin melanjutkan?',
    confirmLabel = 'Ya',
    cancelLabel = 'Batal',
    type = 'danger',
    onConfirm,
    className,
}: ConfirmFormProps) {
    const { confirm } = useAlert();
    const confirmedRef = useRef(false);

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        if (onConfirm) {
            event.preventDefault();

            if (confirmedRef.current) {
                confirmedRef.current = false;
                return;
            }

            const ok = await confirm({
                title,
                message,
                type,
                confirmLabel,
                cancelLabel,
            });

            if (ok) {
                onConfirm();
            }

            return;
        }

        if (confirmedRef.current) {
            confirmedRef.current = false;
            return;
        }

        event.preventDefault();

        const ok = await confirm({
            title,
            message,
            type,
            confirmLabel,
            cancelLabel,
        });

        if (ok) {
            confirmedRef.current = true;
            event.currentTarget.requestSubmit();
        }
    };

    return (
        <form onSubmit={handleSubmit} className={className}>
            {children}
        </form>
    );
}
