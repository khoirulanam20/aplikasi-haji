import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

interface ConfirmDeleteDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    message: string;
    confirmLabel?: string;
    onConfirm: () => void;
    processing?: boolean;
}

export default function ConfirmDeleteDialog({
    open,
    onOpenChange,
    title,
    message,
    confirmLabel = 'Hapus',
    onConfirm,
    processing = false,
}: ConfirmDeleteDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                </DialogHeader>
                <p className="text-sm text-text-secondary">{message}</p>
                <div className="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" onClick={() => onOpenChange(false)}>
                        Batal
                    </Button>
                    <Button variant="destructive" disabled={processing} onClick={onConfirm}>
                        {confirmLabel}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
