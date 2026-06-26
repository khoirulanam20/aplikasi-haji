import { useState } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

const STEPS = [
    {
        title: 'Rencana modul',
        body: (
            <>
                Tentukan judul UI, <strong>kode modul</strong> (hanya <code>a-z</code>,{' '}
                <code>0-9</code>, <code>_</code>), jenis layout, URL, route name utama, grup
                sidebar, dan aksi RBAC yang dibutuhkan sebelum menulis kode.
            </>
        ),
    },
    {
        title: 'Controller & halaman',
        body: (
            <>
                Buat controller di <code>app/Http/Controllers/Superadmin/</code> dan halaman
                view/Inertia. Ikuti pola modul existing seperti Master Data atau Web Setting.
            </>
        ),
    },
    {
        title: 'Route & middleware',
        body: (
            <>
                Daftarkan route di <code>routes/web.php</code> dalam grup prefix{' '}
                <code>/app</code>. Gunakan middleware{' '}
                <code>permission.module:{'{slug}'}.index,list</code> agar permission
                di-resolve dari kode modul di database.
            </>
        ),
        code: `Route::get('/laporan', [LaporanController::class, 'index'])
    ->middleware('permission.module:laporan.index,list')
    ->name('laporan.index');`,
    },
    {
        title: 'Proteksi di UI',
        body: (
            <>
                Sembunyikan tombol aksi sesuai permission: Blade <code>@can('laporan.create')</code>{' '}
                atau React <code>usePermission('laporan.create')</code>. Form base: tombol simpan
                hanya jika user punya <code>create</code> dan <code>update</code> bersamaan.
            </>
        ),
    },
    {
        title: 'Daftar modul di halaman ini',
        body: (
            <>
                Klik <strong>Modul</strong>, isi grup, judul, kode, jenis modul, route name yang
                sudah terdaftar (mis. <code>app.laporan.index</code>), icon, centang RBAC, lalu
                simpan. Sistem otomatis membuat permission, menu sidebar, dan memberi akses ke
                role superadmin.
            </>
        ),
    },
    {
        title: 'Seeder (opsional)',
        body: (
            <>
                Untuk environment baru, tambahkan definisi modul di{' '}
                <code>database/seeders/ModuleSeeder.php</code> lalu jalankan seeder dan{' '}
                <code>php artisan permission:cache-reset</code>.
            </>
        ),
    },
    {
        title: 'Assign role & cache',
        body: (
            <>
                Buka <strong>Role & Permission</strong>, centang permission modul untuk role
                target, simpan, reset cache permission, lalu minta user logout dan login ulang.
            </>
        ),
    },
    {
        title: 'Verifikasi',
        body: (
            <>
                Pastikan <code>php artisan route:list</code> menampilkan route, user dengan
                permission mendapat 200, tanpa permission mendapat 403, menu sidebar muncul jika
                modul aktif, dan tombol aksi hanya tampil sesuai hak akses.
            </>
        ),
    },
] as const;

interface ModuleCreationGuideProps {
    variant?: 'icon' | 'button';
    className?: string;
}

export default function ModuleCreationGuide({
    variant = 'icon',
    className,
}: ModuleCreationGuideProps) {
    const [open, setOpen] = useState(false);

    return (
        <>
            {variant === 'icon' ? (
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className={`h-8 w-8 shrink-0 text-primary hover:bg-blue-50 hover:text-primary ${className ?? ''}`}
                    title="Panduan menambah modul"
                    aria-label="Panduan menambah modul"
                    onClick={() => setOpen(true)}
                >
                    <IconoirIcon name="info-circle" className="text-xl" />
                </Button>
            ) : (
                <Button
                    type="button"
                    variant="secondary"
                    className={className}
                    onClick={() => setOpen(true)}
                >
                    <IconoirIcon name="info-circle" className="text-base" />
                    Panduan Modul
                </Button>
            )}

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Panduan Menambah Modul (Best Practice)</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-5 text-sm text-text-primary">
                        <div className="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-text-secondary">
                            <p className="font-medium text-text-primary">Urutan yang benar</p>
                            <p className="mt-1">
                                Rencana → Controller & halaman → Route → Proteksi UI → Daftar modul
                                (halaman ini) → Assign role → Verifikasi.
                            </p>
                            <p className="mt-2 font-medium text-danger">
                                Jangan mendaftarkan modul di UI sebelum route Laravel-nya ada —
                                form akan menolak <code>route_name</code> yang belum terdaftar.
                            </p>
                        </div>

                        <div>
                            <p className="mb-2 font-semibold">Jenis modul</p>
                            <div className="overflow-x-auto rounded-lg border border-border">
                                <table className="w-full text-left text-xs sm:text-sm">
                                    <thead className="bg-gray-50 text-text-secondary">
                                        <tr>
                                            <th className="px-3 py-2 font-medium">Jenis</th>
                                            <th className="px-3 py-2 font-medium">Kapan dipakai</th>
                                            <th className="px-3 py-2 font-medium">Preset RBAC</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        <tr>
                                            <td className="px-3 py-2 font-medium">Table base</td>
                                            <td className="px-3 py-2 text-text-secondary">
                                                Daftar data + CRUD per baris
                                            </td>
                                            <td className="px-3 py-2">
                                                <code>list</code>, <code>show</code>, <code>create</code>,{' '}
                                                <code>edit</code>, <code>update</code>, <code>delete</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td className="px-3 py-2 font-medium">Form base</td>
                                            <td className="px-3 py-2 text-text-secondary">
                                                Satu halaman form + simpan
                                            </td>
                                            <td className="px-3 py-2">
                                                <code>list</code>, <code>read</code>, <code>create</code>,{' '}
                                                <code>update</code>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <p className="mb-2 font-semibold">Aturan penamaan</p>
                            <ul className="list-disc space-y-1 pl-5 text-text-secondary">
                                <li>
                                    Permission selalu <code>{'{kode}'}.{'{aksi}'}</code> (contoh:{' '}
                                    <code>laporan.list</code>)
                                </li>
                                <li>
                                    Route name: <code>app.laporan.index</code> (sesuai{' '}
                                    <code>ADMIN_ROUTE_NAME</code>)
                                </li>
                                <li>
                                    URL path bebas kebab-case, mis. <code>/app/laporan</code>
                                </li>
                            </ul>
                        </div>

                        <ol className="space-y-4">
                            {STEPS.map((step, index) => (
                                <li key={step.title} className="flex gap-3">
                                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                        {index + 1}
                                    </span>
                                    <div className="min-w-0 flex-1 space-y-2">
                                        <p className="font-semibold">{step.title}</p>
                                        <p className="text-text-secondary">{step.body}</p>
                                        {'code' in step && step.code && (
                                            <pre className="overflow-x-auto rounded-md border border-border bg-gray-50 p-3 text-xs">
                                                <code>{step.code}</code>
                                            </pre>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ol>

                        <p className="text-xs text-text-secondary">
                            Dokumentasi lengkap tersedia di file <code>tutor_modul.md</code> di
                            root project.
                        </p>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
