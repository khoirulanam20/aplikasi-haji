import { KeyboardEvent, useState } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import {
    CustomModuleAction,
    isBuiltInModuleAction,
    moduleActionLabel,
    slugifyRbacAction,
} from '@/lib/moduleActions';

interface ModuleRbacChecklistProps {
    builtInActions: string[];
    customActions: CustomModuleAction[];
    enabledActions: string[];
    onCustomActionsChange: (actions: CustomModuleAction[]) => void;
    onEnabledActionsChange: (actions: string[]) => void;
    errors?: {
        custom_actions?: string;
        enabled_actions?: string;
    };
}

export default function ModuleRbacChecklist({
    builtInActions,
    customActions,
    enabledActions,
    onCustomActionsChange,
    onEnabledActionsChange,
    errors,
}: ModuleRbacChecklistProps) {
    const [draftTitle, setDraftTitle] = useState('');
    const [draftError, setDraftError] = useState<string | null>(null);

    const allBuiltInActions = builtInActions;

    const toggleEnabled = (action: string, checked: boolean) => {
        onEnabledActionsChange(
            checked
                ? [...enabledActions, action]
                : enabledActions.filter((item) => item !== action),
        );
    };

    const removeCustom = (action: string) => {
        onCustomActionsChange(customActions.filter((item) => item.action !== action));
        onEnabledActionsChange(enabledActions.filter((item) => item !== action));
    };

    const commitCustom = () => {
        const label = draftTitle.trim();
        if (!label) {
            setDraftError('Judul hak akses wajib diisi.');
            return;
        }

        const action = slugifyRbacAction(label);
        if (!action) {
            setDraftError('Judul harus mengandung huruf atau angka.');
            return;
        }

        if (isBuiltInModuleAction(action, allBuiltInActions)) {
            setDraftError(`"${action}" sudah dipakai sebagai aksi bawaan.`);
            return;
        }

        if (customActions.some((item) => item.action === action)) {
            setDraftError('Hak akses ini sudah ada di daftar.');
            return;
        }

        onCustomActionsChange([...customActions, { action, label }]);
        if (!enabledActions.includes(action)) {
            onEnabledActionsChange([...enabledActions, action]);
        }

        setDraftTitle('');
        setDraftError(null);
    };

    const onDraftKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            commitCustom();
        }
    };

    const renderActionCheckbox = (action: string, label?: string) => (
        <label key={action} className="inline-flex items-center gap-2 text-sm">
            <Checkbox
                checked={enabledActions.includes(action)}
                onCheckedChange={(checked) => toggleEnabled(action, checked === true)}
            />
            {label ?? moduleActionLabel(action)}
        </label>
    );

    return (
        <div className="space-y-3 rounded-lg border border-border p-4">
            <p className="text-sm font-semibold">Hak Akses (RBAC)</p>
            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                {builtInActions.map((action) => renderActionCheckbox(action))}

                {customActions.map((item) => (
                    <label
                        key={item.action}
                        className="inline-flex items-start gap-2 text-sm"
                        title={`Permission: {kode_modul}.${item.action}`}
                    >
                        <Checkbox
                            className="mt-0.5"
                            checked={enabledActions.includes(item.action)}
                            onCheckedChange={(checked) => toggleEnabled(item.action, checked === true)}
                        />
                        <span className="min-w-0 flex-1">{item.label}</span>
                        <button
                            type="button"
                            className="shrink-0 rounded p-0.5 text-text-secondary hover:text-danger"
                            title="Hapus dari daftar"
                            onClick={(e) => {
                                e.preventDefault();
                                removeCustom(item.action);
                            }}
                        >
                            <IconoirIcon name="xmark" className="text-sm" />
                        </button>
                    </label>
                ))}
            </div>

            <div className="space-y-1 border-t border-border pt-3">
                <p className="text-xs font-medium text-text-secondary">Tambah hak akses lain</p>
                <div className="flex gap-2">
                    <Input
                        placeholder="Judul hak akses (mis. Approve)"
                        value={draftTitle}
                        onChange={(e) => {
                            setDraftTitle(e.target.value);
                            setDraftError(null);
                        }}
                        onKeyDown={onDraftKeyDown}
                        className="flex-1"
                    />
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="shrink-0"
                        title="Tambahkan ke daftar"
                        onClick={commitCustom}
                    >
                        <IconoirIcon name="plus" className="text-base" />
                    </Button>
                </div>
                {draftError && <p className="text-xs text-danger">{draftError}</p>}
            </div>
            {(errors?.custom_actions || errors?.enabled_actions) && (
                <p className="text-sm text-danger">
                    {errors.custom_actions ?? errors.enabled_actions}
                </p>
            )}
        </div>
    );
}
