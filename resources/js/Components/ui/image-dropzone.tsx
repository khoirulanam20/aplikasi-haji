import { useEffect, useId, useMemo, useRef } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

interface ImageDropzoneProps {
    label: string;
    accept?: string;
    supportText: string;
    currentUrl?: string | null;
    value: File | null;
    onChange: (file: File | null) => void;
    onRemove?: () => void;
    disabled?: boolean;
    error?: string;
    previewBoxClassName?: string;
}

export default function ImageDropzone({
    label,
    accept = 'image/*',
    supportText,
    currentUrl,
    value,
    onChange,
    onRemove,
    disabled = false,
    error,
    previewBoxClassName = 'h-20 w-20',
}: ImageDropzoneProps) {
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const objectPreview = useMemo(
        () => (value ? URL.createObjectURL(value) : null),
        [value],
    );

    useEffect(() => {
        return () => {
            if (objectPreview) {
                URL.revokeObjectURL(objectPreview);
            }
        };
    }, [objectPreview]);

    const displayUrl = objectPreview ?? currentUrl ?? null;
    const canRemove = Boolean(displayUrl || value) && !disabled;

    const handleFiles = (files: FileList | null) => {
        onChange(files?.[0] ?? null);
    };

    const handleRemove = () => {
        onChange(null);
        if (inputRef.current) {
            inputRef.current.value = '';
        }
        onRemove?.();
    };

    return (
        <div className="space-y-2">
            <p className="text-sm font-medium text-text-primary">{label}</p>

            <div className="flex flex-col gap-4 rounded-lg border border-dashed border-border bg-gray-50/60 p-4 sm:flex-row sm:items-center">
                <div
                    className={cn(
                        'flex shrink-0 items-center justify-center rounded-lg border border-border bg-white p-2',
                        previewBoxClassName,
                    )}
                >
                    {displayUrl ? (
                        <img
                            src={displayUrl}
                            alt={`Preview ${label}`}
                            className="max-h-full max-w-full object-contain"
                        />
                    ) : (
                        <IconoirIcon name="media-image" className="text-2xl text-text-secondary" />
                    )}
                </div>

                <div className="min-w-0 flex-1 space-y-3">
                    <div className="flex flex-wrap items-center gap-3">
                        <Button
                            type="button"
                            size="sm"
                            disabled={disabled}
                            onClick={() => inputRef.current?.click()}
                        >
                            Pilih File
                        </Button>
                        <span className="truncate text-sm text-text-secondary">
                            {value?.name ?? 'Belum ada file'}
                        </span>
                    </div>

                    {canRemove && (
                        <Button type="button" variant="secondary" size="sm" onClick={handleRemove}>
                            <IconoirIcon name="trash" className="text-base" />
                            Hapus gambar
                        </Button>
                    )}
                </div>

                <input
                    ref={inputRef}
                    id={inputId}
                    type="file"
                    accept={accept}
                    disabled={disabled}
                    className="sr-only"
                    onChange={(e) => handleFiles(e.target.files)}
                />
            </div>

            <p className="text-xs text-text-secondary">{supportText}</p>
            <InputError message={error} />
        </div>
    );
}
