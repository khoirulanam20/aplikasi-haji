import { FormEventHandler, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import { useAlert } from '@/Components/Alert/AlertContext';
import AppLayout from '@/Layouts/AppLayout';
import ConfirmDeleteDialog from '@/Components/ConfirmDeleteDialog';
import TablePagination from '@/Components/TablePagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import UserFormFields, { UserFormData } from '@/Components/Admin/Users/UserFormFields';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { usePermission } from '@/hooks/usePermission';
import { PageProps, Paginated, Role } from '@/types';

interface UserRow {
    id: number;
    name: string;
    username: string | null;
    email: string;
    is_active: boolean;
    roles: Array<{ id: number; name: string; title: string }>;
}

interface UsersIndexProps {
    users: Paginated<UserRow>;
    roles: Role[];
    filters: { q: string; role: string; status: string };
}

type UserForm = UserFormData;

function buildFilterQuery(filters: { q: string; role: string; status: string }) {
    return {
        q: filters.q || undefined,
        role: filters.role || undefined,
        status: filters.status || undefined,
    };
}

const emptyUser = (): UserForm => ({
    name: '',
    username: '',
    email: '',
    password: '',
    role: '',
    is_active: true,
});

export default function Index({ users, roles, filters }: UsersIndexProps) {
    const { auth } = usePage<PageProps>().props;
    const { confirm } = useAlert();
    const canCreate = usePermission('users.create');
    const canUpdate = usePermission('users.update');
    const canDelete = usePermission('users.delete');
    const canImpersonate = usePermission('impersonate.start');

    const [createOpen, setCreateOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const [editingUserId, setEditingUserId] = useState<number | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<UserRow | null>(null);
    const [search, setSearch] = useState(filters.q);
    const [roleFilter, setRoleFilter] = useState(filters.role || 'all');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');

    const createForm = useForm(emptyUser());
    const editForm = useForm(emptyUser());

    const applyFilters = (overrides?: Partial<{ q: string; role: string; status: string }>) => {
        const next = {
            q: overrides?.q ?? search,
            role: overrides?.role ?? (roleFilter === 'all' ? '' : roleFilter),
            status: overrides?.status ?? (statusFilter === 'all' ? '' : statusFilter),
        };

        router.get(route('app.users.index'), buildFilterQuery(next), { preserveState: true });
    };

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        applyFilters();
    };

    const openEdit = (user: UserRow) => {
        setEditingUserId(user.id);
        editForm.setData({
            name: user.name,
            username: user.username ?? '',
            email: user.email,
            password: '',
            role: user.roles[0]?.name ?? '',
            is_active: user.is_active,
        });
        setEditOpen(true);
    };

    return (
        <AppLayout header="Pengguna">
            <Head title="Pengguna" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <form onSubmit={submitSearch} className="flex flex-1 flex-col gap-3 lg:flex-row lg:items-end" data-tour="users-search">
                        <div className="flex-1">
                            <Label htmlFor="users-q" className="sr-only">
                                Cari
                            </Label>
                            <Input
                                id="users-q"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari nama, email, username..."
                                className="max-w-md"
                            />
                        </div>
                        <div className="w-full sm:w-44">
                            <Label>Role</Label>
                            <Select
                                value={roleFilter}
                                onValueChange={(value) => {
                                    setRoleFilter(value);
                                    applyFilters({ role: value === 'all' ? '' : value });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua role</SelectItem>
                                    {roles.map((role) => (
                                        <SelectItem key={role.id} value={role.name}>
                                            {role.title || role.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="w-full sm:w-40">
                            <Label>Status</Label>
                            <Select
                                value={statusFilter}
                                onValueChange={(value) => {
                                    setStatusFilter(value);
                                    applyFilters({ status: value === 'all' ? '' : value });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua status</SelectItem>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="inactive">Nonaktif</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </form>
                    {canCreate && (
                        <Button onClick={() => setCreateOpen(true)} data-tour="users-create">
                            <IconoirIcon name="plus" className="text-base" /> Tambah
                        </Button>
                    )}
                </div>

                <div className="card overflow-hidden p-0" data-tour="users-table">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Username</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell className="font-medium">{user.name}</TableCell>
                                    <TableCell>{user.username ?? '-'}</TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>{user.roles[0]?.title ?? user.roles[0]?.name ?? '-'}</TableCell>
                                    <TableCell>
                                        <Badge variant={user.is_active ? 'success' : 'warning'}>
                                            {user.is_active ? 'Aktif' : 'Nonaktif'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {canUpdate && (
                                                <Button variant="ghost" size="icon" onClick={() => openEdit(user)}>
                                                    <IconoirIcon name="edit" className="text-base" />
                                                </Button>
                                            )}
                                            {canImpersonate &&
                                                user.id !== auth.user?.id &&
                                                user.is_active &&
                                                !user.roles.some((role) => role.name === 'superadmin') && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={async () => {
                                                            const ok = await confirm({
                                                                title: 'Impersonate User',
                                                                message: `Masuk sebagai ${user.name}?`,
                                                                type: 'warning',
                                                                confirmLabel: 'Ya',
                                                            });
                                                            if (ok) {
                                                                router.post(route('app.impersonate.start', user.id));
                                                            }
                                                        }}
                                                    >
                                                        <IconoirIcon name="log-in" className="text-base" />
                                                    </Button>
                                                )}
                                            {canDelete && user.id !== auth.user?.id && (
                                                <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(user)}>
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
                        paginator={users}
                        routeName="app.users.index"
                        query={buildFilterQuery(filters)}
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Pengguna</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            createForm.post(route('app.users.store'), {
                                onSuccess: () => {
                                    setCreateOpen(false);
                                    createForm.reset();
                                },
                            });
                        }}
                        className="space-y-4"
                    >
                        <UserFormFields form={createForm} roles={roles} includePassword />
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
                        <DialogTitle>Edit Pengguna</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!editingUserId) return;
                            editForm.put(route('app.users.update', editingUserId), {
                                onSuccess: () => setEditOpen(false),
                            });
                        }}
                        className="space-y-4"
                    >
                        <UserFormFields form={editForm} roles={roles} />
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
                title="Hapus Pengguna"
                message={`Hapus ${deleteTarget?.name}? Tindakan tidak dapat dibatalkan.`}
                onConfirm={() => {
                    if (!deleteTarget) return;
                    router.delete(route('app.users.destroy', deleteTarget.id), {
                        onSuccess: () => setDeleteTarget(null),
                    });
                }}
            />
        </AppLayout>
    );
}
