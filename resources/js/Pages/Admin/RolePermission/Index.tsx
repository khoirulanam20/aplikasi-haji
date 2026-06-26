import { FormEventHandler, useEffect, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import ConfirmDeleteDialog from '@/Components/ConfirmDeleteDialog';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { usePermission } from '@/hooks/usePermission';
import { moduleActionDescription, permissionActionLabel } from '@/lib/moduleActions';
import TablePagination from '@/Components/TablePagination';
import { PermissionGroup, Paginated, Role } from '@/types';

interface RolePermissionIndexProps {
    roles: Paginated<Role>;
    permissionGroups: PermissionGroup[];
    filters: { role_id: number | null };
}

export default function Index({ roles, permissionGroups, filters }: RolePermissionIndexProps) {
    const canCreate = usePermission('roles.create');
    const canUpdate = usePermission('roles.update');
    const canDelete = usePermission('roles.delete');
    const canAssign = usePermission('roles.assign');

    const [createOpen, setCreateOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const [editingRole, setEditingRole] = useState<Role | null>(null);
    const [permissionOpen, setPermissionOpen] = useState(false);
    const [permissionRole, setPermissionRole] = useState<Role | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Role | null>(null);

    const createForm = useForm({
        title: '',
        code: '',
        description: '',
        is_active: true,
    });

    const editForm = useForm({
        title: '',
        code: '',
        description: '',
        is_active: true,
    });

    const permissionForm = useForm<{ permissions: string[] }>({
        permissions: [],
    });

    // Sync permission checkboxes when a different role is selected.
    useEffect(() => {
        if (permissionRole) {
            permissionForm.setData(
                'permissions',
                permissionRole.permissions?.map((p) => p.name) ?? [],
            );
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps -- only reset form when role id changes
    }, [permissionRole?.id]);

    const openEdit = (role: Role) => {
        setEditingRole(role);
        editForm.setData({
            title: role.title ?? role.name,
            code: role.name,
            description: role.description ?? '',
            is_active: role.is_active,
        });
        setEditOpen(true);
    };

    const openPermissions = (role: Role) => {
        setPermissionRole(role);
        setPermissionOpen(true);
    };

    const submitPermissions: FormEventHandler = (e) => {
        e.preventDefault();
        if (!permissionRole) return;
        permissionForm.put(route('app.role-permission.permissions.update', permissionRole.id), {
            onSuccess: () => setPermissionOpen(false),
        });
    };

    return (
        <AppLayout header="Role & Permission">
            <Head title="Role & Permission" />

            <div className="space-y-6">
                <div className="flex justify-end" data-tour="roles-toolbar">
                    {canCreate && (
                        <Button onClick={() => setCreateOpen(true)}>
                            <IconoirIcon name="plus" className="text-base" /> Tambah
                        </Button>
                    )}
                </div>

                <div className="card overflow-hidden p-0" data-tour="roles-table">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama Role</TableHead>
                                <TableHead>Kode</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {roles.data.map((role) => (
                                <TableRow
                                    key={role.id}
                                    className={filters.role_id === role.id ? 'bg-blue-50/50' : ''}
                                >
                                    <TableCell className="font-medium">{role.title ?? role.name}</TableCell>
                                    <TableCell className="font-mono text-xs">{role.name}</TableCell>
                                    <TableCell className="text-text-secondary">{role.description ?? '-'}</TableCell>
                                    <TableCell>
                                        <Badge variant={role.is_active ? 'success' : 'warning'}>
                                            {role.is_active ? 'Aktif' : 'Nonaktif'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {canAssign && (
                                                <Button variant="ghost" size="icon" onClick={() => openPermissions(role)}>
                                                    <IconoirIcon name="shield" className="text-base" />
                                                </Button>
                                            )}
                                            {canUpdate && (
                                                <Button variant="ghost" size="icon" onClick={() => openEdit(role)}>
                                                    <IconoirIcon name="edit" className="text-base" />
                                                </Button>
                                            )}
                                            {canDelete && role.name !== 'superadmin' && (
                                                <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(role)}>
                                                    <IconoirIcon name="trash" className="text-base text-danger" />
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    <TablePagination
                        paginator={roles}
                        routeName="app.role-permission.index"
                        query={{ role_id: filters.role_id ?? undefined }}
                    />
                </div>
            </div>

            <Dialog open={permissionOpen} onOpenChange={setPermissionOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Permission: {permissionRole?.title ?? permissionRole?.name}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitPermissions} className="space-y-5">
                        {(permissionGroups ?? []).map((group) => (
                            <div key={group.code} className="overflow-hidden rounded-lg border border-border">
                                <div className="border-b border-border bg-gray-100 px-4 py-3">
                                    <h4 className="text-sm font-bold uppercase tracking-wide text-primary">
                                        {group.name}
                                    </h4>
                                    <p className="font-mono text-xs text-text-secondary">{group.code}</p>
                                </div>
                                <div className="divide-y divide-border bg-gray-50/30">
                                    {(group.modules ?? []).map((module) => (
                                        <div key={module.code} className="p-4">
                                            <div className="mb-3">
                                                <p className="text-sm font-semibold text-text-primary">{module.label}</p>
                                                <p className="font-mono text-xs text-text-secondary">{module.code}</p>
                                            </div>
                                            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                                {(module.permissions ?? []).map((permission) => (
                                                    <label
                                                        key={permission.name}
                                                        className="flex cursor-pointer items-center gap-2 rounded-md border border-border bg-white px-2 py-1.5 text-sm"
                                                    >
                                                        <Checkbox
                                                            disabled={!canAssign}
                                                            checked={permissionForm.data.permissions.includes(
                                                                permission.name,
                                                            )}
                                                            onCheckedChange={(checked) => {
                                                                const permissions = checked
                                                                    ? [
                                                                          ...permissionForm.data.permissions,
                                                                          permission.name,
                                                                      ]
                                                                    : permissionForm.data.permissions.filter(
                                                                          (name) => name !== permission.name,
                                                                      );
                                                                permissionForm.setData('permissions', permissions);
                                                            }}
                                                        />
                                                        <span
                                                            className="font-medium"
                                                            title={
                                                                moduleActionDescription(
                                                                    permission.name.split('.').pop() ?? '',
                                                                ) ?? undefined
                                                            }
                                                        >
                                                            {permissionActionLabel(
                                                                permission.name,
                                                                permission.display_label,
                                                            )}
                                                        </span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                        {canAssign && (
                            <div className="flex justify-end border-t border-border pt-4">
                                <Button type="submit" disabled={permissionForm.processing}>
                                    Simpan
                                </Button>
                            </div>
                        )}
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Role</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            createForm.post(route('app.role-permission.roles.store'), {
                                onSuccess: () => {
                                    setCreateOpen(false);
                                    createForm.reset();
                                },
                            });
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <Label>Title</Label>
                            <Input value={createForm.data.title} onChange={(e) => createForm.setData('title', e.target.value)} required />
                            <InputError message={createForm.errors.title} />
                        </div>
                        <div>
                            <Label>Code</Label>
                            <Input value={createForm.data.code} onChange={(e) => createForm.setData('code', e.target.value)} required />
                            <InputError message={createForm.errors.code} />
                        </div>
                        <div>
                            <Label>Description</Label>
                            <Input value={createForm.data.description} onChange={(e) => createForm.setData('description', e.target.value)} />
                        </div>
                        <label className="flex items-center gap-2">
                            <Checkbox
                                checked={createForm.data.is_active}
                                onCheckedChange={(checked) => createForm.setData('is_active', checked === true)}
                            />
                            <span className="text-sm">Aktif</span>
                        </label>
                        <div className="flex justify-end">
                            <Button type="submit" disabled={createForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Role</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!editingRole) return;
                            editForm.put(route('app.role-permission.roles.update', editingRole.id), {
                                onSuccess: () => setEditOpen(false),
                            });
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <Label>Title</Label>
                            <Input value={editForm.data.title} onChange={(e) => editForm.setData('title', e.target.value)} required />
                        </div>
                        <div>
                            <Label>Code</Label>
                            <Input value={editForm.data.code} onChange={(e) => editForm.setData('code', e.target.value)} required />
                        </div>
                        <div>
                            <Label>Description</Label>
                            <Input value={editForm.data.description} onChange={(e) => editForm.setData('description', e.target.value)} />
                        </div>
                        <label className="flex items-center gap-2">
                            <Checkbox
                                checked={editForm.data.is_active}
                                onCheckedChange={(checked) => editForm.setData('is_active', checked === true)}
                            />
                            <span className="text-sm">Aktif</span>
                        </label>
                        <div className="flex justify-end">
                            <Button type="submit" disabled={editForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmDeleteDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title="Hapus Role"
                message={`Hapus role ${deleteTarget?.title ?? deleteTarget?.name}?`}
                onConfirm={() => {
                    if (!deleteTarget) return;
                    router.delete(route('app.role-permission.roles.destroy', deleteTarget.id), {
                        onSuccess: () => setDeleteTarget(null),
                    });
                }}
            />
        </AppLayout>
    );
}
