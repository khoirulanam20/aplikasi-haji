import { useState } from 'react';
import IconoirHtml from '@/Components/IconoirHtml';
import IconoirIcon from '@/Components/IconoirIcon';
import IconPickerModal, { IconoirIconOption } from '@/Components/Modules/IconPickerModal';
import { iconoirHtml } from '@/lib/iconoir';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';

interface IconPickerFieldProps {
    value: string;
    onChange: (html: string) => void;
    icons: IconoirIconOption[];
}

export default function IconPickerField({ value, onChange, icons }: IconPickerFieldProps) {
    const [pickerOpen, setPickerOpen] = useState(false);

    return (
        <div className="space-y-2">
            <Label>Icon (Iconoir)</Label>
            <div className="flex flex-wrap items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-md border border-border bg-white text-xl text-primary">
                    <IconoirHtml html={value || iconoirHtml('app-window')} />
                </span>
                <Button type="button" variant="secondary" size="sm" onClick={() => setPickerOpen(true)}>
                    <IconoirIcon name="search" className="text-base" /> Pilih Icon
                </Button>
                <Button type="button" variant="secondary" size="sm" onClick={() => onChange('')}>
                    Hapus
                </Button>
            </div>
            <p className="text-xs text-text-secondary">
                Pilih dari library Iconoir. Disimpan sebagai tag HTML untuk sidebar.
            </p>
            <IconPickerModal
                open={pickerOpen}
                onOpenChange={setPickerOpen}
                icons={icons}
                onSelect={(name) => onChange(iconoirHtml(name))}
            />
        </div>
    );
}
