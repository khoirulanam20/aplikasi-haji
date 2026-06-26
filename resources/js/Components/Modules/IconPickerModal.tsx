import { useMemo, useState } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';

export interface IconoirIconOption {
    name: string;
    label: string;
}

interface IconPickerModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    icons: IconoirIconOption[];
    onSelect: (name: string) => void;
}

export default function IconPickerModal({ open, onOpenChange, icons, onSelect }: IconPickerModalProps) {
    const [query, setQuery] = useState('');

    const filtered = useMemo(() => {
        if (!query.trim()) {
            return icons;
        }

        const q = query.toLowerCase();
        return icons.filter((icon) => icon.name.includes(q) || icon.label.toLowerCase().includes(q));
    }, [icons, query]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Pilih Icon</DialogTitle>
                </DialogHeader>
                <p className="text-sm text-text-secondary">Klik icon untuk memilih.</p>
                <Input
                    type="search"
                    placeholder="Cari icon..."
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    className="mb-4"
                />
                <div className="grid max-h-[50vh] grid-cols-4 gap-2 overflow-y-auto sm:grid-cols-6 md:grid-cols-8">
                    {filtered.map((icon) => (
                        <button
                            key={icon.name}
                            type="button"
                            title={icon.label}
                            className="flex flex-col items-center gap-1 rounded-md border border-border p-2 text-center transition-colors hover:border-primary hover:bg-orange-50"
                            onClick={() => {
                                onSelect(icon.name);
                                onOpenChange(false);
                                setQuery('');
                            }}
                        >
                            <IconoirIcon name={icon.name} className="text-2xl text-primary" />
                            <span className="line-clamp-2 text-[10px] leading-tight text-text-secondary">
                                {icon.label}
                            </span>
                        </button>
                    ))}
                </div>
                <div className="flex justify-end">
                    <Button type="button" variant="secondary" onClick={() => onOpenChange(false)}>
                        Tutup
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
