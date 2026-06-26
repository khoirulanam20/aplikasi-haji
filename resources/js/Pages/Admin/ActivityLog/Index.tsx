import { FormEventHandler, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import ConfirmDeleteDialog from '@/Components/ConfirmDeleteDialog';
import TablePagination from '@/Components/TablePagination';
import { Badge } from '@/Components/ui/badge';
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
import { ActivityLogFilterOptions, ActivityLogItem, Paginated } from '@/types';

interface ActivityLogFilters {
    q: string;
    causer_id: string;
    event: string;
    subject_type: string;
    date_from: string;
    date_to: string;
}

interface ActivityLogIndexProps {
    activities: Paginated<ActivityLogItem>;
    filters: ActivityLogFilters;
    filterOptions: ActivityLogFilterOptions;
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function eventVariant(event: string | null): 'default' | 'success' | 'warning' | 'danger' {
    if (event === 'deleted') return 'danger';
    if (event === 'created') return 'success';
    if (event === 'updated') return 'warning';
    return 'default';
}

function buildFilterQuery(filters: ActivityLogFilters): Record<string, string | undefined> {
    return {
        q: filters.q || undefined,
        causer_id: filters.causer_id || undefined,
        event: filters.event || undefined,
        subject_type: filters.subject_type || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
    };
}

export default function Index({ activities, filters, filterOptions }: ActivityLogIndexProps) {
    const canDelete = usePermission('activitylog.delete');

    const [formFilters, setFormFilters] = useState(filters);
    const [detailTarget, setDetailTarget] = useState<ActivityLogItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<ActivityLogItem | null>(null);

    const submitFilters: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('app.activity-log.index'), buildFilterQuery(formFilters), { preserveState: true });
    };

    const resetFilters = () => {
        const empty: ActivityLogFilters = {
            q: '',
            causer_id: '',
            event: '',
            subject_type: '',
            date_from: '',
            date_to: '',
        };
        setFormFilters(empty);
        router.get(route('app.activity-log.index'), {}, { preserveState: true });
    };

    const properties = detailTarget?.properties as
        | { attributes?: Record<string, unknown>; old?: Record<string, unknown> }
        | null
        | undefined;

    const changeKeys = properties
        ? Array.from(
              new Set([
                  ...Object.keys(properties.attributes ?? {}),
                  ...Object.keys(properties.old ?? {}),
              ]),
          )
        : [];

    return (
        <AppLayout header="Log Aktivitas">
            <Head title="Log Aktivitas" />

            <div className="space-y-6">
                <form onSubmit={submitFilters} className="card space-y-4" data-tour="activitylog-filters">
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <Label htmlFor="q">Cari</Label>
                            <Input
                                id="q"
                                value={formFilters.q}
                                onChange={(e) => setFormFilters((prev) => ({ ...prev, q: e.target.value }))}
                                placeholder="Cari deskripsi..."
                            />
                        </div>
                        <div>
                            <Label>Pengguna</Label>
                            <Select
                                value={formFilters.causer_id || 'all'}
                                onValueChange={(value) =>
                                    setFormFilters((prev) => ({
                                        ...prev,
                                        causer_id: value === 'all' ? '' : value,
                                    }))
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua pengguna" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua pengguna</SelectItem>
                                    {filterOptions.causers.map((user) => (
                                        <SelectItem key={user.id} value={String(user.id)}>
                                            {user.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>Event</Label>
                            <Select
                                value={formFilters.event || 'all'}
                                onValueChange={(value) =>
                                    setFormFilters((prev) => ({
                                        ...prev,
                                        event: value === 'all' ? '' : value,
                                    }))
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua event" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua event</SelectItem>
                                    {filterOptions.events.map((event) => (
                                        <SelectItem key={event} value={event}>
                                            {event}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>Tipe Subject</Label>
                            <Select
                                value={formFilters.subject_type || 'all'}
                                onValueChange={(value) =>
                                    setFormFilters((prev) => ({
                                        ...prev,
                                        subject_type: value === 'all' ? '' : value,
                                    }))
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua tipe</SelectItem>
                                    {filterOptions.subject_types.map((type) => (
                                        <SelectItem key={type.value} value={type.value}>
                                            {type.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="date_from">Dari Tanggal</Label>
                            <Input
                                id="date_from"
                                type="date"
                                value={formFilters.date_from}
                                onChange={(e) =>
                                    setFormFilters((prev) => ({ ...prev, date_from: e.target.value }))
                                }
                            />
                        </div>
                        <div>
                            <Label htmlFor="date_to">Sampai Tanggal</Label>
                            <Input
                                id="date_to"
                                type="date"
                                value={formFilters.date_to}
                                onChange={(e) =>
                                    setFormFilters((prev) => ({ ...prev, date_to: e.target.value }))
                                }
                            />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button type="submit">
                            <IconoirIcon name="filter" className="text-base" /> Terapkan Filter
                        </Button>
                        <Button type="button" variant="secondary" onClick={resetFilters}>
                            Reset
                        </Button>
                    </div>
                </form>

                <div className="card overflow-hidden p-0" data-tour="activitylog-table">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Waktu</TableHead>
                                <TableHead>Pengguna</TableHead>
                                <TableHead>Event</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead>Subject</TableHead>
                                <TableHead className="text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {activities.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-text-secondary">
                                        Belum ada log aktivitas.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                activities.data.map((activity) => (
                                    <TableRow key={activity.id}>
                                        <TableCell className="whitespace-nowrap text-sm">
                                            {formatDateTime(activity.created_at)}
                                        </TableCell>
                                        <TableCell>{activity.causer?.name ?? '-'}</TableCell>
                                        <TableCell>
                                            {activity.event ? (
                                                <Badge variant={eventVariant(activity.event)}>
                                                    {activity.event}
                                                </Badge>
                                            ) : (
                                                '-'
                                            )}
                                        </TableCell>
                                        <TableCell>{activity.description}</TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                <div className="font-medium">
                                                    {activity.subject_label ?? '-'}
                                                </div>
                                                <div className="text-text-secondary">
                                                    {activity.subject_type_label}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    title="Detail"
                                                    onClick={() => setDetailTarget(activity)}
                                                >
                                                    <IconoirIcon name="eye" className="text-base" />
                                                </Button>
                                                {canDelete && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        title="Hapus"
                                                        onClick={() => setDeleteTarget(activity)}
                                                    >
                                                        <IconoirIcon
                                                            name="trash"
                                                            className="text-base text-danger"
                                                        />
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                    <TablePagination
                        paginator={activities}
                        routeName="app.activity-log.index"
                        query={buildFilterQuery(filters)}
                    />
                </div>
            </div>

            <Dialog open={detailTarget !== null} onOpenChange={(open) => !open && setDetailTarget(null)}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Detail Log Aktivitas</DialogTitle>
                    </DialogHeader>
                    {detailTarget && (
                        <div className="space-y-4">
                            <dl className="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt className="text-text-secondary">Waktu</dt>
                                    <dd>{formatDateTime(detailTarget.created_at)}</dd>
                                </div>
                                <div>
                                    <dt className="text-text-secondary">Event</dt>
                                    <dd>{detailTarget.event ?? '-'}</dd>
                                </div>
                                <div>
                                    <dt className="text-text-secondary">Pengguna</dt>
                                    <dd>
                                        {detailTarget.causer
                                            ? `${detailTarget.causer.name} (${detailTarget.causer.email})`
                                            : '-'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-text-secondary">Subject</dt>
                                    <dd>
                                        {detailTarget.subject_label ?? '-'} — {detailTarget.subject_type_label}
                                    </dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-text-secondary">Deskripsi</dt>
                                    <dd>{detailTarget.description}</dd>
                                </div>
                            </dl>

                            {changeKeys.length > 0 && (
                                <div>
                                    <h4 className="mb-2 text-sm font-medium">Perubahan Atribut</h4>
                                    <div className="overflow-hidden rounded-md border">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Atribut</TableHead>
                                                    <TableHead>Sebelum</TableHead>
                                                    <TableHead>Sesudah</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {changeKeys.map((key) => (
                                                    <TableRow key={key}>
                                                        <TableCell className="font-medium">{key}</TableCell>
                                                        <TableCell>
                                                            {formatPropertyValue(properties?.old?.[key])}
                                                        </TableCell>
                                                        <TableCell>
                                                            {formatPropertyValue(properties?.attributes?.[key])}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            <ConfirmDeleteDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title="Hapus Log Aktivitas"
                message={`Hapus log "${deleteTarget?.description}"?`}
                onConfirm={() => {
                    if (!deleteTarget) return;
                    router.delete(route('app.activity-log.destroy', deleteTarget.id), {
                        onSuccess: () => setDeleteTarget(null),
                    });
                }}
            />
        </AppLayout>
    );
}

function formatPropertyValue(value: unknown): string {
    if (value === null || value === undefined) {
        return '-';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
}
