import { Head, Link } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import UserAvatar from '@/Components/UserAvatar';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

interface DashboardProps {
    stats: {
        users: number;
        roles: number;
        master_data: number;
    };
    laravelVersion: string;
    phpVersion: string;
}

const placeholderRows = [
    { name: 'Budi Santoso', email: 'budi@example.com', role: 'Admin', active: true, date: '29 Mei 2026' },
    { name: 'Siti Aminah', email: 'siti@example.com', role: 'Editor', active: true, date: '28 Mei 2026' },
    { name: 'Andi Wijaya', email: 'andi@example.com', role: 'Viewer', active: false, date: '27 Mei 2026' },
    { name: 'Dewi Lestari', email: 'dewi@example.com', role: 'Editor', active: true, date: '26 Mei 2026' },
    { name: 'Rizky Pratama', email: 'rizky@example.com', role: 'Admin', active: true, date: '25 Mei 2026' },
    { name: 'Maya Sari', email: 'maya@example.com', role: 'Viewer', active: false, date: '24 Mei 2026' },
];

function SectionToolbar({
    title,
    children,
}: {
    title: string;
    children?: React.ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-3">
            <h2 className="text-lg font-bold text-text-primary">{title}</h2>
            {children && <div className="flex flex-wrap items-center gap-2">{children}</div>}
        </div>
    );
}

function MetricCard({
    icon,
    iconClassName,
    label,
    value,
    trend,
}: {
    icon: string;
    iconClassName: string;
    label: string;
    value: string | number;
    trend?: string;
}) {
    return (
        <div className="card">
            <div className="flex items-start gap-4">
                <div
                    className={`flex h-14 w-14 shrink-0 items-center justify-center rounded-full border ${iconClassName}`}
                >
                    <IconoirIcon name={icon} className="text-3xl" />
                </div>
                <div className="min-w-0 flex-1">
                    <p className="text-sm font-medium text-text-secondary">{label}</p>
                    <p className="mt-1 text-2xl font-bold text-text-primary">{value}</p>
                    <p className="mt-1 text-xs text-text-secondary">{trend ?? '— dari bulan lalu'}</p>
                </div>
            </div>
        </div>
    );
}

function ChartPlaceholderCard({
    title,
    summary,
    className,
}: {
    title: string;
    summary: string;
    className?: string;
}) {
    return (
        <div className={`card ${className ?? ''}`}>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 className="text-sm font-bold text-text-primary">{title}</h3>
                    <p className="mt-1 text-xs text-text-secondary">{summary}</p>
                </div>
                <select
                    disabled
                    className="rounded-md border border-border bg-gray-50 px-3 py-1.5 text-xs text-text-secondary"
                    defaultValue="7d"
                >
                    <option value="7d">7 hari terakhir</option>
                    <option value="30d">30 hari terakhir</option>
                </select>
            </div>
            <div className="flex h-[280px] items-center justify-center rounded-md border border-dashed border-border text-sm text-text-secondary">
                Area chart placeholder
            </div>
        </div>
    );
}

function ActivityTable() {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead className="w-10">
                        <Checkbox disabled aria-label="Pilih semua" />
                    </TableHead>
                    <TableHead>Pengguna</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Tanggal</TableHead>
                    <TableHead className="w-10" />
                </TableRow>
            </TableHeader>
            <TableBody>
                {placeholderRows.map((row) => (
                    <TableRow key={row.email}>
                        <TableCell>
                            <Checkbox disabled aria-label={`Pilih ${row.name}`} />
                        </TableCell>
                        <TableCell>
                            <div className="flex items-center gap-3">
                                <UserAvatar
                                    user={{
                                        name: row.name,
                                        avatar_url: null,
                                        avatar_initial: row.name.charAt(0),
                                    }}
                                />
                                <span className="font-medium text-text-primary">{row.name}</span>
                            </div>
                        </TableCell>
                        <TableCell className="text-text-secondary">{row.email}</TableCell>
                        <TableCell>{row.role}</TableCell>
                        <TableCell>
                            <Badge variant={row.active ? 'success' : 'default'}>
                                {row.active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                        </TableCell>
                        <TableCell className="text-text-secondary">{row.date}</TableCell>
                        <TableCell>
                            <button
                                type="button"
                                className="rounded-md p-1 text-text-secondary hover:bg-gray-100"
                                aria-label="Menu aksi"
                            >
                                <IconoirIcon name="more-vert" className="text-lg" />
                            </button>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

export default function Dashboard({ stats, laravelVersion, phpVersion }: DashboardProps) {
    return (
        <AppLayout header="Dashboard">
            <Head title="Dashboard" />

            <div className="space-y-8">
                <div className="card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-tour="dashboard-welcome">
                    <div className="space-y-1">
                        <h2 className="text-lg font-bold text-text-primary">Selamat datang di Dashboard</h2>
                        <p className="text-sm text-text-secondary">
                            Ringkasan aktivitas dan statistik aplikasi Anda.
                        </p>
                        <div className="flex flex-wrap items-center gap-2 pt-2">
                            <Badge className="badge-success px-3">Laravel {laravelVersion}</Badge>
                            <Badge variant="warning" className="px-3">
                                PHP {phpVersion}
                            </Badge>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('app.master-data.index')}>
                            <IconoirIcon name="database" className="text-lg" />
                            Kelola Master Data
                        </Link>
                    </Button>
                </div>

                <section className="space-y-4">
                    <SectionToolbar title="Overview">
                        <Input
                            type="text"
                            readOnly
                            value="1 Mei 2026 — 29 Mei 2026"
                            className="h-9 w-44 cursor-default text-xs"
                        />
                        <Button variant="secondary" size="sm" disabled>
                            <IconoirIcon name="download" className="text-base" />
                            Export
                        </Button>
                    </SectionToolbar>

                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-tour="dashboard-stats">
                        <MetricCard
                            icon="group"
                            iconClassName="border-orange-100 bg-orange-50 text-primary"
                            label="Total User"
                            value={stats.users}
                        />
                        <MetricCard
                            icon="shield"
                            iconClassName="border-blue-100 bg-blue-50 text-blue-600"
                            label="Total Role"
                            value={stats.roles}
                        />
                        <MetricCard
                            icon="database"
                            iconClassName="border-green-100 bg-green-50 text-success"
                            label="Master Data"
                            value={stats.master_data}
                        />
                        <MetricCard
                            icon="view-grid"
                            iconClassName="border-purple-100 bg-purple-50 text-purple-600"
                            label="Modul Aktif"
                            value="—"
                            trend="Placeholder statistik"
                        />
                    </div>

                    <div className="grid gap-6 lg:grid-cols-5" data-tour="dashboard-charts">
                        <ChartPlaceholderCard
                            className="lg:col-span-3"
                            title="Aktivitas Pengguna"
                            summary="Ringkasan kunjungan dan aksi pengguna"
                        />
                        <ChartPlaceholderCard
                            className="lg:col-span-2"
                            title="Pertumbuhan Data"
                            summary="Tren penambahan data bulanan"
                        />
                    </div>
                </section>

                <section className="space-y-4" data-tour="dashboard-activity">
                    <SectionToolbar title="Aktivitas Terbaru">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('app.users.index')}>Lihat semua</Link>
                        </Button>
                        <Input
                            type="text"
                            readOnly
                            value="Mei 2026"
                            className="h-9 w-28 cursor-default text-xs"
                        />
                        <Button variant="secondary" size="sm" disabled>
                            <IconoirIcon name="download" className="text-base" />
                            Export
                        </Button>
                    </SectionToolbar>

                    <div className="card overflow-hidden p-0">
                        <Tabs defaultValue="all" className="px-4 pt-4">
                            <TabsList>
                                <TabsTrigger value="all">Semua</TabsTrigger>
                                <TabsTrigger value="active">Aktif</TabsTrigger>
                                <TabsTrigger value="inactive">Nonaktif</TabsTrigger>
                            </TabsList>
                            <TabsContent value="all">
                                <ActivityTable />
                            </TabsContent>
                            <TabsContent value="active">
                                <ActivityTable />
                            </TabsContent>
                            <TabsContent value="inactive">
                                <ActivityTable />
                            </TabsContent>
                        </Tabs>

                        <div className="flex items-center justify-end gap-2 border-t border-border px-4 py-3">
                            <button
                                type="button"
                                disabled
                                className="rounded-md px-3 py-1 text-sm text-text-secondary"
                            >
                                &laquo;
                            </button>
                            <span className="rounded-md bg-primary px-3 py-1 text-sm text-white">1</span>
                            <button
                                type="button"
                                disabled
                                className="rounded-md px-3 py-1 text-sm text-text-secondary hover:bg-gray-100"
                            >
                                2
                            </button>
                            <button
                                type="button"
                                disabled
                                className="rounded-md px-3 py-1 text-sm text-text-secondary hover:bg-gray-100"
                            >
                                &raquo;
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
