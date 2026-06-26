import { FormEventHandler, useEffect, useMemo, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import WilayahSelectFields, {
    findDesaKode,
    findKecamatanKode,
} from '@/Components/Admin/HajjParticipants/WilayahSelectFields';
import ImportProgressToast from '@/Components/Admin/HajjParticipants/ImportProgressToast';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { usePermission } from '@/hooks/usePermission';
import { useHajjImportJob } from '@/hooks/useHajjImportJob';
import {
    HajjFlash,
    HajjImportJobState,
    HajjImportPreview,
    HajjParticipantItem,
    PageProps,
    Paginated,
    WilayahOptions,
} from '@/types';

type ParticipantFormData = {
    tahun_haji: string;
    nomor_porsi: string;
    nama: string;
    alamat: string;
    kecamatan_kode: string;
    desa_kode: string;
    telepon: string;
    kloter: string;
    rombongan: string;
    regu: string;
    email: string;
};

const emptyForm = (tahunHaji: string): ParticipantFormData => ({
    tahun_haji: tahunHaji,
    nomor_porsi: '',
    nama: '',
    alamat: '',
    kecamatan_kode: '',
    desa_kode: '',
    telepon: '',
    kloter: '',
    rombongan: '',
    regu: '',
    email: '',
});

interface HajjParticipantsIndexProps {
    items: Paginated<HajjParticipantItem>;
    filters: { q: string; tahun_haji: string; kecamatan_kode: string; desa_kode: string };
    tahunOptions: number[];
    wilayah: WilayahOptions;
}

function buildFilterQuery(filters: {
    q: string;
    tahun_haji: string;
    kecamatan_kode: string;
    desa_kode: string;
}) {
    return {
        q: filters.q || undefined,
        tahun_haji: filters.tahun_haji || undefined,
        kecamatan_kode: filters.kecamatan_kode || undefined,
        desa_kode: filters.desa_kode || undefined,
    };
}

export default function Index({ items, filters, tahunOptions, wilayah }: HajjParticipantsIndexProps) {
    const { hajjFlash } = usePage<PageProps>().props;
    const currentYear = new Date().getFullYear();
    const defaultTahunHaji = String(
        tahunOptions.includes(currentYear) ? currentYear : (tahunOptions[0] ?? currentYear),
    );
    const canCreate = usePermission('hajj_participants.create');
    const canUpdate = usePermission('hajj_participants.update');
    const canDelete = usePermission('hajj_participants.delete');
    const canImport = usePermission('hajj_participants.import');

    const [createOpen, setCreateOpen] = useState(false);
    const [importOpen, setImportOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<HajjParticipantItem | null>(null);
    const [search, setSearch] = useState(filters.q);
    const [tahunFilter, setTahunFilter] = useState(filters.tahun_haji || 'all');
    const [kecamatanFilter, setKecamatanFilter] = useState(filters.kecamatan_kode || 'all');
    const [desaFilter, setDesaFilter] = useState(filters.desa_kode || 'all');
    const [credentialDialog, setCredentialDialog] = useState<HajjFlash | null>(null);
    const [importSummary, setImportSummary] = useState<HajjFlash['import_summary']>(null);
    const [importResultOpen, setImportResultOpen] = useState(false);
    const [importStep, setImportStep] = useState<'upload' | 'preview'>('upload');
    const [importPreview, setImportPreview] = useState<HajjImportPreview | null>(null);
    const [importJobToken, setImportJobToken] = useState<string | null>(null);
    const [importJob, setImportJob] = useState<HajjImportJobState | null>(null);
    const [importError, setImportError] = useState<string | null>(null);

    const createForm = useForm<ParticipantFormData>(emptyForm(defaultTahunHaji));
    const editForm = useForm<Omit<ParticipantFormData, 'email'>>(emptyForm(defaultTahunHaji));
    const importForm = useForm<{
        file: File | null;
        tahun_haji: string;
        duplicate_action: 'skip' | 'replace';
    }>({
        file: null,
        tahun_haji: defaultTahunHaji,
        duplicate_action: 'skip',
    });

    const importFormBusy = importForm.processing && !importJobToken;
    const importBusy = importFormBusy || !!importJobToken;

    const importProgressToast = useMemo(() => {
        if (importJobToken) {
            return {
                title: 'Mengimport peserta haji',
                description: 'Proses berjalan di background. Jangan tutup halaman ini.',
                processed: importJob?.processed ?? 0,
                total: importJob?.total ?? 0,
            };
        }

        if (importForm.processing && importStep === 'upload') {
            return {
                title: 'Menguji file Excel',
                description: 'Membaca dan memvalidasi data dari file...',
            };
        }

        if (importForm.processing && importStep === 'preview') {
            return {
                title: 'Memulai import',
                description: 'Mengirim data ke antrian...',
            };
        }

        return null;
    }, [importJobToken, importJob, importForm.processing, importStep]);

    const importableCount = useMemo(() => {
        if (!importPreview) {
            return 0;
        }

        const duplicateDb = importPreview.stats.duplicate_database ?? 0;
        const replaceDb =
            importForm.data.duplicate_action === 'replace' ? duplicateDb : 0;

        return importPreview.stats.ready + replaceDb;
    }, [importPreview, importForm.data.duplicate_action]);

    useEffect(() => {
        if (hajjFlash?.generated_password) {
            setCredentialDialog(hajjFlash);
        }
        if (hajjFlash?.import_summary) {
            setImportSummary(hajjFlash.import_summary);
            setImportResultOpen(true);
            setImportOpen(false);
            setImportStep('upload');
            setImportPreview(null);
            importForm.reset();
        }
        if (hajjFlash?.import_preview) {
            setImportPreview(hajjFlash.import_preview);
            setImportStep('preview');
            setImportError(null);
        }
        if (hajjFlash?.import_job_token) {
            setImportJobToken(hajjFlash.import_job_token);
            setImportError(null);
            setImportOpen(false);
        }
    }, [hajjFlash]);

    useHajjImportJob({
        token: importJobToken,
        onProgress: (state) => setImportJob(state),
        onCompleted: (summary) => {
            setImportJobToken(null);
            setImportJob(null);
            setImportSummary(summary);
            setImportResultOpen(true);
            setImportOpen(false);
            setImportStep('upload');
            setImportPreview(null);
            importForm.reset();
            router.reload({ only: ['items'] });
        },
        onFailed: (message) => {
            setImportJobToken(null);
            setImportJob(null);
            setImportError(message);
        },
    });

    const desaFilterOptions = useMemo(() => {
        if (kecamatanFilter === 'all') {
            return [];
        }

        return wilayah.kecamatan.find((item) => item.kode === kecamatanFilter)?.desa ?? [];
    }, [wilayah, kecamatanFilter]);

    const applyFilters = (overrides?: Partial<{
        q: string;
        tahun_haji: string;
        kecamatan_kode: string;
        desa_kode: string;
    }>) => {
        const kecamatan =
            overrides?.kecamatan_kode ??
            (kecamatanFilter === 'all' ? '' : kecamatanFilter);
        const desa =
            overrides?.desa_kode ?? (desaFilter === 'all' ? '' : desaFilter);

        router.get(
            route('app.hajj-participants.index'),
            buildFilterQuery({
                q: overrides?.q ?? search,
                tahun_haji:
                    overrides?.tahun_haji ??
                    (tahunFilter === 'all' ? '' : tahunFilter),
                kecamatan_kode: kecamatan,
                desa_kode: kecamatan ? desa : '',
            }),
            { preserveState: true },
        );
    };

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        applyFilters();
    };

    const applyTahunFilter = (value: string) => {
        setTahunFilter(value);
        applyFilters({ tahun_haji: value === 'all' ? '' : value });
    };

    const applyKecamatanFilter = (value: string) => {
        setKecamatanFilter(value);
        setDesaFilter('all');
        applyFilters({
            kecamatan_kode: value === 'all' ? '' : value,
            desa_kode: '',
        });
    };

    const applyDesaFilter = (value: string) => {
        setDesaFilter(value);
        applyFilters({ desa_kode: value === 'all' ? '' : value });
    };

    return (
        <AppLayout header="Peserta Haji">
            <Head title="Peserta Haji" />

            <div className="space-y-6">
                <form
                    onSubmit={submitSearch}
                    className="flex w-full flex-nowrap items-center gap-2"
                >
                    <Input
                        id="participants-q"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari nama, porsi, desa..."
                        className="h-10 min-w-[10rem] flex-1 max-w-sm"
                    />
                    <div className="shrink-0">
                        <Select value={tahunFilter} onValueChange={applyTahunFilter}>
                            <SelectTrigger className="h-10 w-[8.5rem]">
                                <SelectValue placeholder="Tahun haji" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tahun</SelectItem>
                                {tahunOptions.map((year) => (
                                    <SelectItem key={year} value={String(year)}>
                                        {year}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="shrink-0">
                        <Select value={kecamatanFilter} onValueChange={applyKecamatanFilter}>
                            <SelectTrigger className="h-10 w-[9.5rem]">
                                <SelectValue placeholder="Kecamatan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Kecamatan</SelectItem>
                                {wilayah.kecamatan.map((item) => (
                                    <SelectItem key={item.kode} value={item.kode}>
                                        {item.nama}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="shrink-0">
                        <Select
                            value={desaFilter}
                            onValueChange={applyDesaFilter}
                            disabled={kecamatanFilter === 'all'}
                        >
                            <SelectTrigger className="h-10 w-[9.5rem]">
                                <SelectValue
                                    placeholder={
                                        kecamatanFilter === 'all' ? 'Pilih kecamatan' : 'Desa'
                                    }
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Desa</SelectItem>
                                {desaFilterOptions.map((item) => (
                                    <SelectItem key={item.kode} value={item.kode}>
                                        {item.nama}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="ml-auto flex shrink-0 items-center gap-2">
                        {canImport && (
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => {
                                    setImportStep('upload');
                                    setImportPreview(null);
                                    setImportError(null);
                                    setImportJobToken(null);
                                    setImportJob(null);
                                    importForm.reset();
                                    setImportOpen(true);
                                }}
                            >
                                <IconoirIcon name="upload" className="text-base" /> Import Excel
                            </Button>
                        )}
                        {canCreate && (
                            <Button type="button" onClick={() => setCreateOpen(true)}>
                                <IconoirIcon name="plus" className="text-base" /> Tambah
                            </Button>
                        )}
                    </div>
                </form>

                <div className="card overflow-hidden p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tahun</TableHead>
                                <TableHead>No. Porsi</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Desa</TableHead>
                                <TableHead>Penugasan</TableHead>
                                <TableHead>Akun</TableHead>
                                <TableHead className="text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {items.data.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell>{item.tahun_haji}</TableCell>
                                    <TableCell>{item.nomor_porsi ?? '-'}</TableCell>
                                    <TableCell className="font-medium">{item.nama}</TableCell>
                                    <TableCell>{item.desa ?? '-'}</TableCell>
                                    <TableCell>
                                        {[item.kloter, item.rombongan, item.regu].filter(Boolean).join(' / ') || '-'}
                                    </TableCell>
                                    <TableCell>
                                        {item.user ? (
                                            <span className={item.user.is_active ? 'text-success' : 'text-muted-foreground'}>
                                                {item.user.email}
                                            </span>
                                        ) : (
                                            '-'
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex justify-end gap-1">
                                            {canUpdate && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => {
                                                        setEditId(item.id);
                                                        const kecamatanKode = findKecamatanKode(wilayah, item.kecamatan);
                                                        editForm.setData({
                                                            tahun_haji: String(item.tahun_haji),
                                                            nomor_porsi: item.nomor_porsi ?? '',
                                                            nama: item.nama,
                                                            alamat: item.alamat ?? '',
                                                            kecamatan_kode: kecamatanKode,
                                                            desa_kode: findDesaKode(wilayah, kecamatanKode, item.desa),
                                                            telepon: item.telepon ?? '',
                                                            kloter: item.kloter ?? '',
                                                            rombongan: item.rombongan ?? '',
                                                            regu: item.regu ?? '',
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
                        routeName="app.hajj-participants.index"
                        query={buildFilterQuery(filters)}
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Tambah Peserta Haji</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            createForm.post(route('app.hajj-participants.store'), {
                                onSuccess: () => {
                                    setCreateOpen(false);
                                    createForm.reset();
                                },
                            });
                        }}
                        className="space-y-4"
                    >
                        <ParticipantFields form={createForm} tahunOptions={tahunOptions} wilayah={wilayah} includeEmail />
                        <div className="flex justify-end">
                            <Button type="submit" disabled={createForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editId !== null} onOpenChange={(open) => !open && setEditId(null)}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Edit Peserta Haji</DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!editId) return;
                            editForm.put(route('app.hajj-participants.update', editId), {
                                onSuccess: () => setEditId(null),
                            });
                        }}
                        className="space-y-4"
                    >
                        <ParticipantFields form={editForm} tahunOptions={tahunOptions} wilayah={wilayah} />
                        <div className="flex justify-end">
                            <Button type="submit" disabled={editForm.processing}>
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={importOpen}
                onOpenChange={(open) => {
                    if (!open && importFormBusy) {
                        return;
                    }

                    setImportOpen(open);
                    if (!open) {
                        setImportStep('upload');
                        setImportPreview(null);
                        if (!importJobToken) {
                            setImportError(null);
                            setImportJobToken(null);
                            setImportJob(null);
                            importForm.reset();
                        }
                    }
                }}
            >
                <DialogContent
                    className={importStep === 'preview' ? 'sm:max-w-4xl' : 'sm:max-w-lg'}
                >
                    <DialogHeader>
                        <DialogTitle>
                            {importStep === 'upload' ? 'Import Excel' : 'Preview Import Excel'}
                        </DialogTitle>
                    </DialogHeader>

                    {importStep === 'upload' ? (
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                importForm.post(route('app.hajj-participants.import.preview'), {
                                    forceFormData: true,
                                    preserveState: true,
                                });
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label>Tahun Haji</Label>
                                <Select
                                    value={importForm.data.tahun_haji}
                                    onValueChange={(value) => {
                                        importForm.setData('tahun_haji', value);
                                        setImportPreview(null);
                                        setImportStep('upload');
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih tahun" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {tahunOptions.map((year) => (
                                            <SelectItem key={year} value={String(year)}>
                                                {year}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={importForm.errors.tahun_haji} />
                            </div>
                            <div>
                                <div className="flex items-center justify-between gap-2">
                                    <Label htmlFor="import-excel-file">File Excel (.xlsx)</Label>
                                    <a
                                        href={route('app.hajj-participants.import.template')}
                                        className="inline-flex items-center gap-1 text-sm text-primary hover:underline"
                                    >
                                        <IconoirIcon name="download" className="text-base" />
                                        Unduh template
                                    </a>
                                </div>
                                <Input
                                    id="import-excel-file"
                                    type="file"
                                    accept=".xlsx"
                                    className="mt-1.5 block min-h-10 cursor-pointer file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-primary-hover"
                                    onChange={(e) => {
                                        importForm.setData('file', e.target.files?.[0] ?? null);
                                        setImportPreview(null);
                                        setImportStep('upload');
                                    }}
                                    required
                                />
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    Isi data sesuai template. Kecamatan dan desa pilih dari dropdown (data Kemendagri
                                    Kab. Temanggung). Tahun haji dipilih di atas, bukan di file Excel.
                                </p>
                                <InputError message={importForm.errors.file} />
                            </div>
                            <div className="flex justify-end">
                                <Button type="submit" disabled={importFormBusy || !importForm.data.file}>
                                    {importForm.processing ? (
                                        <>
                                            <IconoirIcon name="refresh-double" className="animate-spin text-base" />
                                            Menguji...
                                        </>
                                    ) : (
                                        'Tes File'
                                    )}
                                </Button>
                            </div>
                        </form>
                    ) : (
                        importPreview && (
                            <div className="space-y-4">
                                <div className="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-5">
                                    <PreviewStat label="Siap import" value={importPreview.stats.ready} tone="success" />
                                    <PreviewStat
                                        label="Duplikat DB"
                                        value={importPreview.stats.duplicate_database ?? 0}
                                        tone="warning"
                                    />
                                    <PreviewStat
                                        label="Duplikat file"
                                        value={importPreview.stats.duplicate_file ?? 0}
                                        tone="warning"
                                    />
                                    <PreviewStat label="Baris kosong" value={importPreview.stats.empty} />
                                    <PreviewStat label="Total data" value={importPreview.stats.total} />
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Sheet: <span className="font-medium">{importPreview.sheet}</span> · Header baris{' '}
                                    {importPreview.header_row} · Tahun {importPreview.tahun_haji}
                                    {importPreview.stats.total > importPreview.rows.length &&
                                        ` · Menampilkan ${importPreview.rows.length} dari ${importPreview.stats.total} baris`}
                                </p>

                                <div className="card overflow-hidden p-0">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="w-16">Baris</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>No. Porsi</TableHead>
                                                <TableHead>Nama</TableHead>
                                                <TableHead>Alamat</TableHead>
                                                <TableHead>Desa</TableHead>
                                                <TableHead>Penugasan</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {importPreview.rows.map((row) => (
                                                <TableRow key={row.row}>
                                                    <TableCell>{row.row}</TableCell>
                                                    <TableCell>
                                                        <PreviewStatusBadge
                                                            status={row.status}
                                                            message={row.message}
                                                            duplicateSource={row.duplicate_source}
                                                        />
                                                    </TableCell>
                                                    <TableCell>{row.data.nomor_porsi ?? '-'}</TableCell>
                                                    <TableCell className="font-medium">{row.data.nama}</TableCell>
                                                    <TableCell>{row.data.alamat ?? '-'}</TableCell>
                                                    <TableCell>{row.data.desa ?? '-'}</TableCell>
                                                    <TableCell>
                                                        {[row.data.kloter, row.data.rombongan, row.data.regu]
                                                            .filter(Boolean)
                                                            .join(' / ') || '-'}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>

                                {(importPreview.stats.duplicate_database ?? 0) > 0 && (
                                    <div className="space-y-3 rounded-md border border-border bg-muted/30 p-4">
                                        <p className="text-sm font-medium">Duplikat di database</p>
                                        <label className="flex cursor-pointer items-start gap-3 text-sm">
                                            <input
                                                type="radio"
                                                name="duplicate_action"
                                                className="mt-1"
                                                checked={importForm.data.duplicate_action === 'skip'}
                                                onChange={() => importForm.setData('duplicate_action', 'skip')}
                                            />
                                            <span>
                                                <span className="font-medium">Lewati</span> — pertahankan data yang
                                                sudah ada ({importPreview.stats.duplicate_database ?? 0} baris)
                                            </span>
                                        </label>
                                        <label className="flex cursor-pointer items-start gap-3 text-sm">
                                            <input
                                                type="radio"
                                                name="duplicate_action"
                                                className="mt-1"
                                                checked={importForm.data.duplicate_action === 'replace'}
                                                onChange={() => importForm.setData('duplicate_action', 'replace')}
                                            />
                                            <span>
                                                <span className="font-medium">Ganti</span> — timpa data lama dengan
                                                isi Excel ({importPreview.stats.duplicate_database ?? 0} baris)
                                            </span>
                                        </label>
                                    </div>
                                )}

                                {(importPreview.stats.duplicate_file ?? 0) > 0 && (
                                    <p className="text-sm text-muted-foreground">
                                        {importPreview.stats.duplicate_file ?? 0} duplikat dalam file Excel tetap dilewati.
                                    </p>
                                )}

                                {importableCount === 0 ? (
                                    <p className="text-sm text-danger">
                                        Tidak ada baris yang siap diproses. Periksa file atau tahun haji yang dipilih.
                                    </p>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        {importForm.data.duplicate_action === 'replace' &&
                                        (importPreview.stats.duplicate_database ?? 0) > 0
                                            ? `${importPreview.stats.ready} baris baru + ${importPreview.stats.duplicate_database ?? 0} baris akan diganti.`
                                            : importPreview.stats.duplicate > 0
                                              ? `${importPreview.stats.duplicate} baris duplikat akan dilewati saat import.`
                                              : 'Semua baris data siap diimport.'}
                                    </p>
                                )}

                                {importError && (
                                    <p className="rounded-md border border-danger/30 bg-danger/5 px-3 py-2 text-sm text-danger">
                                        {importError}
                                    </p>
                                )}

                                <div className="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        disabled={importFormBusy}
                                        onClick={() => {
                                            setImportStep('upload');
                                            setImportPreview(null);
                                            setImportError(null);
                                        }}
                                    >
                                        Kembali
                                    </Button>
                                    <Button
                                        disabled={importFormBusy || importableCount === 0}
                                        onClick={() => {
                                            setImportError(null);
                                            setImportJob({
                                                status: 'queued',
                                                processed: 0,
                                                total: importableCount,
                                            });
                                            importForm.post(route('app.hajj-participants.import'), {
                                                forceFormData: true,
                                                preserveState: true,
                                            });
                                        }}
                                    >
                                        {importFormBusy ? (
                                            <>
                                                <IconoirIcon name="refresh-double" className="animate-spin text-base" />
                                                Memulai...
                                            </>
                                        ) : (
                                            `Import ${importableCount} Peserta`
                                        )}
                                    </Button>
                                </div>
                            </div>
                        )
                    )}
                </DialogContent>
            </Dialog>

            <Dialog open={!!credentialDialog} onOpenChange={(open) => !open && setCredentialDialog(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Akun peserta dibuat</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-2 text-sm">
                        <p>Simpan kredensial berikut — password hanya ditampilkan sekali.</p>
                        <p>
                            <span className="font-medium">Username:</span> {credentialDialog?.generated_username}
                        </p>
                        <p>
                            <span className="font-medium">Email:</span> {credentialDialog?.generated_email}
                        </p>
                        <p>
                            <span className="font-medium">Password:</span>{' '}
                            <code className="rounded bg-muted px-1">{credentialDialog?.generated_password}</code>
                        </p>
                    </div>
                    <div className="flex justify-end">
                        <Button onClick={() => setCredentialDialog(null)}>Tutup</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog open={importResultOpen} onOpenChange={setImportResultOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hasil Import</DialogTitle>
                    </DialogHeader>
                    {importSummary && (
                        <div className="space-y-3 text-sm">
                            <p>Berhasil ditambahkan: {importSummary.imported}</p>
                            {(importSummary.replaced ?? 0) > 0 && <p>Diganti: {importSummary.replaced}</p>}
                            <p>Dilewati (duplikat): {importSummary.skipped}</p>
                            {importSummary.errors.length > 0 && (
                                <div>
                                    <p className="font-medium text-danger">Error:</p>
                                    <ul className="mt-1 max-h-40 list-disc overflow-y-auto pl-5">
                                        {importSummary.errors.map((error) => (
                                            <li key={error}>{error}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                    )}
                    <div className="flex justify-end">
                        <Button onClick={() => setImportResultOpen(false)}>Tutup</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <ConfirmDeleteDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title="Hapus Peserta Haji"
                message={`Hapus ${deleteTarget?.nama}? Akun terkait akan dinonaktifkan.`}
                onConfirm={() => {
                    if (!deleteTarget) return;
                    router.delete(route('app.hajj-participants.destroy', deleteTarget.id), {
                        onSuccess: () => setDeleteTarget(null),
                    });
                }}
            />
            {importProgressToast && (
                <ImportProgressToast
                    title={importProgressToast.title}
                    description={importProgressToast.description}
                    processed={importProgressToast.processed}
                    total={importProgressToast.total}
                />
            )}
            {importError && !importOpen && (
                <ImportProgressToast
                    variant="error"
                    title="Import gagal"
                    description={importError}
                />
            )}
        </AppLayout>
    );
}

function PreviewStat({
    label,
    value,
    tone,
}: {
    label: string;
    value: number;
    tone?: 'success' | 'warning';
}) {
    const color =
        tone === 'success' ? 'text-success' : tone === 'warning' ? 'text-warning' : 'text-foreground';

    return (
        <div className="rounded-lg border p-3">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className={`text-2xl font-semibold ${color}`}>{value}</p>
        </div>
    );
}

function PreviewStatusBadge({
    status,
    message,
    duplicateSource,
}: {
    status: HajjImportPreview['rows'][number]['status'];
    message: string | null;
    duplicateSource?: HajjImportPreview['rows'][number]['duplicate_source'];
}) {
    if (status === 'ready') {
        return <span className="text-success">Siap</span>;
    }

    const label = duplicateSource === 'database' ? 'Duplikat DB' : 'Duplikat file';

    return (
        <span className="text-warning" title={message ?? undefined}>
            {label}
        </span>
    );
}

function ParticipantFields({
    form,
    tahunOptions,
    wilayah,
    includeEmail = false,
}: {
    form: {
        data: ParticipantFormData | Omit<ParticipantFormData, 'email'>;
        setData: (key: string, value: string) => void;
        errors: Partial<Record<string, string>>;
    };
    tahunOptions: number[];
    wilayah: WilayahOptions;
    includeEmail?: boolean;
}) {
    const { data, setData, errors } = form;

    return (
        <>
            <div>
                <Label>Tahun Haji</Label>
                <Select value={data.tahun_haji} onValueChange={(value) => setData('tahun_haji', value)}>
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        {tahunOptions.map((year) => (
                            <SelectItem key={year} value={String(year)}>
                                {year}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.tahun_haji} />
            </div>
            <div>
                <Label>Nomor Porsi</Label>
                <Input value={data.nomor_porsi} onChange={(e) => setData('nomor_porsi', e.target.value)} />
                <InputError message={errors.nomor_porsi} />
            </div>
            <div>
                <Label>Nama</Label>
                <Input value={data.nama} onChange={(e) => setData('nama', e.target.value)} required />
                <InputError message={errors.nama} />
            </div>
            <div>
                <Label>Alamat</Label>
                <Input value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} />
                <InputError message={errors.alamat} />
            </div>
            <WilayahSelectFields
                wilayah={wilayah}
                kecamatanKode={data.kecamatan_kode}
                desaKode={data.desa_kode}
                onKecamatanChange={(value) => setData('kecamatan_kode', value)}
                onDesaChange={(value) => setData('desa_kode', value)}
                errors={{
                    kecamatan_kode: errors.kecamatan_kode,
                    desa_kode: errors.desa_kode,
                }}
            />
            <div>
                <Label>Telepon</Label>
                <Input value={data.telepon} onChange={(e) => setData('telepon', e.target.value)} />
                <InputError message={errors.telepon} />
            </div>
            <div className="grid gap-4 sm:grid-cols-3">
                <div>
                    <Label>Kloter</Label>
                    <Input value={data.kloter} onChange={(e) => setData('kloter', e.target.value)} />
                    <InputError message={errors.kloter} />
                </div>
                <div>
                    <Label>Rombongan</Label>
                    <Input value={data.rombongan} onChange={(e) => setData('rombongan', e.target.value)} />
                    <InputError message={errors.rombongan} />
                </div>
                <div>
                    <Label>Regu</Label>
                    <Input value={data.regu} onChange={(e) => setData('regu', e.target.value)} />
                    <InputError message={errors.regu} />
                </div>
            </div>
            {includeEmail && 'email' in data && (
                <div>
                    <Label>Email akun (opsional)</Label>
                    <Input
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="Kosongkan untuk email otomatis"
                    />
                    <InputError message={errors.email} />
                </div>
            )}
        </>
    );
}
