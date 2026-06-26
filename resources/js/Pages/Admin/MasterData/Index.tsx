import { FormEventHandler, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import ConfirmDeleteDialog from '@/Components/ConfirmDeleteDialog';
import InputError from '@/Components/InputError';
import TablePagination from '@/Components/TablePagination';
import { Button } from '@/Components/ui/button';
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
import { usePermission } from '@/hooks/usePermission';
import { MasterDataItem, Paginated } from '@/types';

interface MasterDataIndexProps {
    items: Paginated<MasterDataItem>;
    filters: { q: string };
}

export default function Index({ items, filters }: MasterDataIndexProps) {
    const canCreate = usePermission('masterdata.create');
    const canUpdate = usePermission('masterdata.update');
    const canDelete = usePermission('masterdata.delete');

    const [createOpen, setCreateOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<MasterDataItem | null>(null);
    const [search, setSearch] = useState(filters.q);

    const createForm = useForm({ name: '', description: '' });
    const editForm = useForm({ name: '', description: '' });

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('app.master-data.index'), { q: search }, { preserveState: true });
    };

    return (
        <AppLayout header="Master Data">
            <Head title="Master Data" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <form onSubmit={submitSearch} className="flex-1" data-tour="masterdata-search">
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari nama..."
                            className="max-w-md"
                        />
                    </form>
                    {canCreate && (
                        <Button onClick={() => setCreateOpen(true)} data-tour="masterdata-create">
                            <IconoirIcon name="plus" className="text-base" /> Tambah
                        </Button>
                    )}
                </div>

                <div className="card overflow-hidden p-0" data-tour="masterdata-table">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead className="text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {items.data.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell className="font-medium">{item.name}</TableCell>
                                    <TableCell>{item.description ?? '-'}</TableCell>
                                    <TableCell>
                                        <div className="flex justify-end gap-1">
                                            {canUpdate && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => {
                                                        setEditId(item.id);
                                                        editForm.setData({
                                                            name: item.name,
                                                            description: item.description ?? '',
                                                        });
                                                    }}
                                                >
                                                    <IconoirIcon name="edit" className="text-base" />
                                                </Button>
                                            )}
                                            {canDelete && (
                                                <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(item)}>
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
                        paginator={items}
                        routeName="app.master-data.index"
                        query={{ q: filters.q || undefined }}
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Master Data</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            createForm.post(route('app.master-data.store'), {
                                onSuccess: () => {
                                    setCreateOpen(false);
                                    createForm.reset();
                                },
                            });
                        }}
                        className="space-y-4"
                    >
                        <MasterDataFields form={createForm} />
                        <div className="flex justify-end">
                            <Button type="submit" disabled={createForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editId !== null} onOpenChange={(open) => !open && setEditId(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Master Data</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!editId) return;
                            editForm.put(route('app.master-data.update', editId), {
                                onSuccess: () => setEditId(null),
                            });
                        }}
                        className="space-y-4"
                    >
                        <MasterDataFields form={editForm} />
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
                title="Hapus Master Data"
                message={`Hapus ${deleteTarget?.name}?`}
                onConfirm={() => {
                    if (!deleteTarget) return;
                    router.delete(route('app.master-data.destroy', deleteTarget.id), {
                        onSuccess: () => setDeleteTarget(null),
                    });
                }}
            />
        </AppLayout>
    );
}

function MasterDataFields({ form }: { form: ReturnType<typeof useForm<{ name: string; description: string }>> }) {
    return (
        <>
            <div>
                <Label>Nama</Label>
                <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                <InputError message={form.errors.name} />
            </div>
            <div>
                <Label>Deskripsi</Label>
                <Input value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                <InputError message={form.errors.description} />
            </div>
        </>
    );
}
