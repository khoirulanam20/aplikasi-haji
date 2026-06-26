import { FormEventHandler, useRef, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import ConfirmForm from '@/Components/ConfirmForm';
import IconoirHtml from '@/Components/IconoirHtml';
import IconoirIcon from '@/Components/IconoirIcon';
import IconPickerField from '@/Components/Modules/IconPickerField';
import { IconoirIconOption } from '@/Components/Modules/IconPickerModal';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { useModulesReorder } from '@/hooks/useModulesReorder';
import { usePermission } from '@/hooks/usePermission';
import ModuleCreationGuide from '@/Components/Modules/ModuleCreationGuide';
import ModuleRbacChecklist from '@/Components/Modules/ModuleRbacChecklist';
import { CustomModuleAction, isBuiltInModuleAction } from '@/lib/moduleActions';
import AppLayout from '@/Layouts/AppLayout';
import { ModuleGroupRecord, ModuleRecord } from '@/types';

interface ModulesIndexProps {
    groups: ModuleGroupRecord[];
    allGroups: Array<{ id: number; name: string; code: string }>;
    actions: string[];
    layoutTypes: Record<string, string>;
    actionPresets: Record<string, string[]>;
    iconoirIcons: IconoirIconOption[];
    reorderUrls: {
        groups: string;
        modules: string;
    };
}

interface EditGroup {
    id: number | null;
    name: string;
    code: string;
    sort_order: number;
    is_active: boolean;
}

interface EditModule {
    id: number | null;
    module_group_id: string;
    title: string;
    code: string;
    route_name: string;
    icon: string;
    description: string;
    is_active: boolean;
    show_in_sidebar: boolean;
    layout_type: string;
    enabled_actions: string[];
}

const emptyGroup = (): EditGroup => ({
    id: null,
    name: '',
    code: '',
    sort_order: 0,
    is_active: true,
});

const emptyModule = (actionPresets: Record<string, string[]>): EditModule => ({
    id: null,
    module_group_id: '',
    title: '',
    code: '',
    route_name: '',
    icon: '',
    description: '',
    is_active: true,
    show_in_sidebar: true,
    layout_type: 'table_base',
    enabled_actions: actionPresets.table_base ?? [],
});

export default function Index({
    groups,
    allGroups,
    actions,
    layoutTypes,
    actionPresets,
    iconoirIcons,
    reorderUrls,
}: ModulesIndexProps) {
    const canCreateGroup = usePermission('module-groups.create');
    const canUpdateGroup = usePermission('module-groups.update');
    const canDeleteGroup = usePermission('module-groups.delete');
    const canCreateModule = usePermission('modules.create');
    const canUpdateModule = usePermission('modules.update');
    const canDeleteModule = usePermission('modules.delete');
    const canShowModule = usePermission('modules.show');

    const rootRef = useRef<HTMLDivElement>(null);
    useModulesReorder(rootRef, reorderUrls, canUpdateGroup, canUpdateModule);

    const [createGroupOpen, setCreateGroupOpen] = useState(false);
    const [editGroupOpen, setEditGroupOpen] = useState(false);
    const [moduleFormOpen, setModuleFormOpen] = useState(false);
    const [editGroup, setEditGroup] = useState<EditGroup>(emptyGroup());
    const [editModule, setEditModule] = useState<EditModule>(emptyModule(actionPresets));
    const [customActions, setCustomActions] = useState<CustomModuleAction[]>([]);

    const createGroupForm = useForm({
        name: '',
        code: '',
        is_active: true,
    });

    const editGroupForm = useForm({
        name: '',
        code: '',
        is_active: true,
    });

    const moduleForm = useForm({
        module_group_id: String(allGroups[0]?.id ?? ''),
        title: '',
        code: '',
        layout_type: 'table_base',
        route_name: '',
        icon: '',
        description: '',
        is_active: true,
        show_in_sidebar: true,
        enabled_actions: actionPresets.table_base ?? [],
    });

    const isEditingModule = () => editModule.id !== null;

    const openModuleCreate = () => {
        const empty = emptyModule(actionPresets);
        empty.module_group_id = String(allGroups[0]?.id ?? '');
        setEditModule(empty);
        moduleForm.setData({
            module_group_id: empty.module_group_id,
            title: '',
            code: '',
            layout_type: 'table_base',
            route_name: '',
            icon: '',
            description: '',
            is_active: true,
            show_in_sidebar: true,
            enabled_actions: actionPresets.table_base ?? [],
        });
        setCustomActions([]);
        setModuleFormOpen(true);
    };

    const customActionsFromModule = (module: ModuleRecord): CustomModuleAction[] =>
        (module.actions ?? [])
            .filter((item) => !isBuiltInModuleAction(item.action, actions))
            .map((item) => ({
                action: item.action,
                label: item.label ?? item.action,
            }));

    const openModuleEdit = (module: ModuleRecord) => {
        const payload: EditModule = {
            id: module.id,
            module_group_id: String(module.module_group_id),
            title: module.title,
            code: module.code,
            route_name: module.route_name ?? '',
            icon: module.icon ?? '',
            description: module.description ?? '',
            is_active: module.is_active,
            show_in_sidebar: module.show_in_sidebar,
            layout_type: module.layout_type ?? 'table_base',
            enabled_actions:
                module.actions?.filter((a) => a.is_enabled).map((a) => a.action) ??
                actionPresets[module.layout_type] ??
                [],
        };
        setEditModule(payload);
        setCustomActions(customActionsFromModule(module));
        moduleForm.setData({
            module_group_id: payload.module_group_id,
            title: payload.title,
            code: payload.code,
            layout_type: payload.layout_type,
            route_name: payload.route_name,
            icon: payload.icon,
            description: payload.description,
            is_active: payload.is_active,
            show_in_sidebar: payload.show_in_sidebar,
            enabled_actions: payload.enabled_actions,
        });
        setModuleFormOpen(true);
    };

    const openGroupEdit = (group: ModuleGroupRecord) => {
        const payload: EditGroup = {
            id: group.id,
            name: group.name,
            code: group.code,
            sort_order: group.sort_order,
            is_active: group.is_active,
        };
        setEditGroup(payload);
        editGroupForm.setData({
            name: payload.name,
            code: payload.code,
            is_active: payload.is_active,
        });
        setEditGroupOpen(true);
    };

    const submitModule: FormEventHandler = (e) => {
        e.preventDefault();
        const payload = {
            ...moduleForm.data,
            module_group_id: Number(moduleForm.data.module_group_id),
            custom_actions: customActions.map(({ action, label }) => ({ action, label })),
        };

        if (isEditingModule()) {
            router.put(route('app.modules.update', editModule.id!), payload as never, {
                onSuccess: () => setModuleFormOpen(false),
            });
        } else {
            router.post(route('app.modules.store'), payload as never, {
                onSuccess: () => setModuleFormOpen(false),
            });
        }
    };

    return (
        <AppLayout
            header={
                <span className="inline-flex items-center gap-1.5">
                    Manajemen Modul
                    <ModuleCreationGuide />
                </span>
            }
        >
            <Head title="Manajemen Modul" />

            <div ref={rootRef} id="modules-order-root" className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-2" data-tour="modules-toolbar">
                    <ModuleCreationGuide variant="button" className="sm:hidden" />
                    <div className="flex flex-wrap justify-end gap-2 sm:ml-auto">
                    {canCreateGroup && (
                        <Button variant="secondary" onClick={() => setCreateGroupOpen(true)}>
                            <IconoirIcon name="plus" className="text-base" /> Grup Modul
                        </Button>
                    )}
                    {canCreateModule && (
                        <Button onClick={openModuleCreate}>
                            <IconoirIcon name="plus" className="text-base" /> Modul
                        </Button>
                    )}
                    </div>
                </div>

                <div id="module-group-list" className="space-y-6" data-tour="modules-list">
                    {groups.length === 0 ? (
                        <div className="card text-center text-text-secondary">Belum ada grup modul.</div>
                    ) : (
                        groups.map((group) => (
                            <div
                                key={group.id}
                                className="card module-group-card"
                                data-id={group.id}
                            >
                                <div className="mb-3 flex items-center justify-between gap-2">
                                    <div className="flex min-w-0 items-center gap-2">
                                        {canUpdateGroup && (
                                            <button
                                                type="button"
                                                className="group-drag-handle shrink-0 cursor-grab p-1 text-text-secondary hover:text-text-primary active:cursor-grabbing"
                                                title="Geser grup"
                                            >
                                                <IconoirIcon name="drag-hand-gesture" className="text-lg" />
                                            </button>
                                        )}
                                        <h3 className="truncate text-base font-bold text-text-primary">
                                            {group.name}
                                        </h3>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-0.5">
                                        {canUpdateGroup && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-8 w-8 text-text-secondary"
                                                title="Edit grup"
                                                onClick={() => openGroupEdit(group)}
                                            >
                                                <IconoirIcon name="edit" className="text-lg" />
                                            </Button>
                                        )}
                                        {canDeleteGroup && (
                                            <ConfirmForm
                                                title="Hapus Grup Modul"
                                                message={
                                                    group.modules.length > 0
                                                        ? `Hapus grup «${group.name}»? ${group.modules.length} modul di dalamnya ikut dihapus.`
                                                        : `Hapus grup «${group.name}»?`
                                                }
                                                confirmLabel="Hapus"
                                                onConfirm={() =>
                                                    router.delete(route('app.module-groups.destroy', group.id))
                                                }
                                                className="inline-flex"
                                            >
                                                <Button
                                                    type="submit"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8"
                                                    title="Hapus grup"
                                                >
                                                    <IconoirIcon name="trash" className="text-lg text-danger" />
                                                </Button>
                                            </ConfirmForm>
                                        )}
                                    </div>
                                </div>

                                <div data-module-list={group.id} className="space-y-2">
                                    {group.modules.length === 0 ? (
                                        <p className="rounded-md border border-dashed border-border px-4 py-6 text-center text-sm text-text-secondary">
                                            Belum ada modul di grup ini.
                                        </p>
                                    ) : (
                                        group.modules.map((module) => (
                                            <div
                                                key={module.id}
                                                className="module-row flex items-center gap-3 rounded-md border border-border bg-white px-3 py-2.5"
                                                data-id={module.id}
                                            >
                                                {canUpdateModule && (
                                                    <button
                                                        type="button"
                                                        className="module-drag-handle shrink-0 cursor-grab p-1 text-text-secondary hover:text-text-primary active:cursor-grabbing"
                                                        title="Geser modul"
                                                    >
                                                        <IconoirIcon
                                                            name="drag-hand-gesture"
                                                            className="text-lg"
                                                        />
                                                    </button>
                                                )}
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-border bg-gray-50 text-lg text-text-secondary">
                                                    {module.icon ? (
                                                        <IconoirHtml html={module.icon} />
                                                    ) : (
                                                        <IconoirIcon name="view-grid" />
                                                    )}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate font-semibold text-text-primary">
                                                        {module.title}
                                                    </p>
                                                    <p className="text-xs font-medium uppercase tracking-wide text-text-secondary">
                                                        {module.code}
                                                    </p>
                                                    {module.show_in_sidebar && !module.route_name && (
                                                        <p className="mt-0.5 text-xs font-medium text-danger">
                                                            Route kosong
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="flex shrink-0 items-center gap-0.5">
                                                    {canShowModule && (
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            className="h-8 w-8 text-text-secondary hover:text-text-primary"
                                                            asChild
                                                            title="Detail modul"
                                                        >
                                                            <Link href={route('app.modules.show', module.id)}>
                                                                <IconoirIcon name="eye" className="text-lg" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {canUpdateModule && (
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            className="h-8 w-8 text-text-secondary hover:text-text-primary"
                                                            title="Edit modul"
                                                            onClick={() => openModuleEdit(module)}
                                                        >
                                                            <IconoirIcon name="edit" className="text-lg" />
                                                        </Button>
                                                    )}
                                                    {canDeleteModule && (
                                                        <ConfirmForm
                                                            title="Hapus Modul"
                                                            message="Hapus modul ini? Permission terkait ikut dihapus."
                                                            confirmLabel="Hapus"
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    route('app.modules.destroy', module.id),
                                                                )
                                                            }
                                                            className="inline-flex"
                                                        >
                                                            <Button
                                                                type="submit"
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-8 w-8"
                                                                title="Hapus modul"
                                                            >
                                                                <IconoirIcon
                                                                    name="trash"
                                                                    className="text-lg text-danger"
                                                                />
                                                            </Button>
                                                        </ConfirmForm>
                                                    )}
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>

            <Dialog open={createGroupOpen} onOpenChange={setCreateGroupOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Grup Modul</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            createGroupForm.post(route('app.module-groups.store'), {
                                onSuccess: () => {
                                    setCreateGroupOpen(false);
                                    createGroupForm.reset();
                                },
                            });
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <Label>Nama Grup</Label>
                            <Input
                                value={createGroupForm.data.name}
                                onChange={(e) => createGroupForm.setData('name', e.target.value)}
                                required
                            />
                            <InputError message={createGroupForm.errors.name} />
                        </div>
                        <div>
                            <Label>Kode Grup</Label>
                            <Input
                                value={createGroupForm.data.code}
                                onChange={(e) => createGroupForm.setData('code', e.target.value)}
                                placeholder="contoh: master"
                                required
                            />
                            <InputError message={createGroupForm.errors.code} />
                        </div>
                        <label className="inline-flex items-center gap-2">
                            <Checkbox
                                checked={createGroupForm.data.is_active}
                                onCheckedChange={(checked) =>
                                    createGroupForm.setData('is_active', checked === true)
                                }
                            />
                            Aktif
                        </label>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="secondary" onClick={() => setCreateGroupOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={createGroupForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editGroupOpen} onOpenChange={setEditGroupOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Grup Modul</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!editGroup.id) return;
                            editGroupForm.put(route('app.module-groups.update', editGroup.id), {
                                onSuccess: () => setEditGroupOpen(false),
                            });
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <Label>Nama Grup</Label>
                            <Input
                                value={editGroupForm.data.name}
                                onChange={(e) => editGroupForm.setData('name', e.target.value)}
                                required
                            />
                        </div>
                        <div>
                            <Label>Kode Grup</Label>
                            <Input
                                value={editGroupForm.data.code}
                                onChange={(e) => editGroupForm.setData('code', e.target.value)}
                                required
                            />
                        </div>
                        <label className="inline-flex items-center gap-2">
                            <Checkbox
                                checked={editGroupForm.data.is_active}
                                onCheckedChange={(checked) =>
                                    editGroupForm.setData('is_active', checked === true)
                                }
                            />
                            Aktif
                        </label>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="secondary" onClick={() => setEditGroupOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={editGroupForm.processing}>
                                Update
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={moduleFormOpen} onOpenChange={setModuleFormOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            {isEditingModule() ? 'Edit Modul' : 'Tambah Modul'}
                            {!isEditingModule() && <ModuleCreationGuide />}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitModule} className="space-y-4">
                        <div>
                            <Label>Grup Modul</Label>
                            <Select
                                value={moduleForm.data.module_group_id}
                                onValueChange={(value) => moduleForm.setData('module_group_id', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {allGroups.map((group) => (
                                        <SelectItem key={group.id} value={String(group.id)}>
                                            {group.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>Judul Modul</Label>
                            <Input
                                value={moduleForm.data.title}
                                onChange={(e) => moduleForm.setData('title', e.target.value)}
                                required
                            />
                            <InputError message={moduleForm.errors.title} />
                        </div>
                        <div>
                            <Label>Kode Modul</Label>
                            <Input
                                value={moduleForm.data.code}
                                onChange={(e) => moduleForm.setData('code', e.target.value)}
                                required
                            />
                            <InputError message={moduleForm.errors.code} />
                        </div>

                        <div className="space-y-3 rounded-lg border border-border p-4">
                            <p className="text-sm font-semibold">Sidebar</p>
                            <div>
                                <Label>Route name (Laravel)</Label>
                                <Input
                                    value={moduleForm.data.route_name}
                                    onChange={(e) => moduleForm.setData('route_name', e.target.value)}
                                    placeholder="app.master-data.index"
                                />
                                <p className="mt-1 text-xs text-text-secondary">
                                    Gunakan nama route yang sudah terdaftar, bukan URL path.
                                </p>
                                <InputError message={moduleForm.errors.route_name} />
                            </div>
                            <IconPickerField
                                value={moduleForm.data.icon}
                                onChange={(html) => moduleForm.setData('icon', html)}
                                icons={iconoirIcons}
                            />
                            <label className="inline-flex items-center gap-2">
                                <Checkbox
                                    checked={moduleForm.data.show_in_sidebar}
                                    onCheckedChange={(checked) =>
                                        moduleForm.setData('show_in_sidebar', checked === true)
                                    }
                                />
                                Tampil di sidebar
                            </label>
                        </div>

                        <div className="space-y-3 rounded-lg border border-border p-4">
                            <p className="text-sm font-semibold">Jenis modul</p>
                            <div className="flex flex-wrap gap-4">
                                {Object.entries(layoutTypes).map(([value, label]) => (
                                    <label key={value} className="inline-flex items-center gap-2 text-sm">
                                        <input
                                            type="radio"
                                            name="layout_type"
                                            value={value}
                                            checked={moduleForm.data.layout_type === value}
                                            onChange={() => {
                                                moduleForm.setData('layout_type', value);
                                                moduleForm.setData(
                                                    'enabled_actions',
                                                    actionPresets[value] ?? [],
                                                );
                                            }}
                                        />
                                        {label}
                                    </label>
                                ))}
                            </div>
                        </div>

                        <ModuleRbacChecklist
                            builtInActions={actions}
                            customActions={customActions}
                            enabledActions={moduleForm.data.enabled_actions}
                            onCustomActionsChange={setCustomActions}
                            onEnabledActionsChange={(enabled_actions) =>
                                moduleForm.setData('enabled_actions', enabled_actions)
                            }
                            errors={{
                                custom_actions:
                                    (moduleForm.errors as Record<string, string | undefined>)
                                        .custom_actions,
                                enabled_actions: moduleForm.errors.enabled_actions,
                            }}
                        />

                        <div>
                            <Label>Deskripsi</Label>
                            <textarea
                                className="input min-h-[80px] w-full"
                                rows={3}
                                value={moduleForm.data.description}
                                onChange={(e) => moduleForm.setData('description', e.target.value)}
                            />
                        </div>
                        <label className="inline-flex items-center gap-2">
                            <Checkbox
                                checked={moduleForm.data.is_active}
                                onCheckedChange={(checked) =>
                                    moduleForm.setData('is_active', checked === true)
                                }
                            />
                            Aktif
                        </label>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="secondary" onClick={() => setModuleFormOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={moduleForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
