import { Head, Link } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import GuestLayout from '@/Layouts/GuestLayout';

export default function AccessPending() {
    return (
        <GuestLayout>
            <Head title="Access Pending" />
            <div className="card mx-auto max-w-lg space-y-4 text-center">
                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-warning/10 text-warning">
                    <IconoirIcon name="warning-circle" className="text-3xl" />
                </div>
                <h1 className="text-xl font-bold text-text-primary">Akses belum tersedia</h1>
                <p className="text-sm text-text-secondary">
                    Akun Anda sudah masuk, tetapi belum memiliki role atau permission untuk mengakses dashboard.
                    Hubungi administrator untuk menetapkan role dan centang permission modul (mis.{' '}
                    <strong>Dashboard → view</strong>).
                </p>
                <div className="pt-2">
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="btn-primary inline-flex w-full justify-center"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}
