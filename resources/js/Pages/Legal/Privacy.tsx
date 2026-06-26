import { Head } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Privacy() {
    return (
        <GuestLayout>
            <Head title="Kebijakan Privasi" />

            <div className="mx-auto max-w-2xl space-y-4 text-sm text-text-secondary">
                <h1 className="text-2xl font-bold text-text-primary">Kebijakan Privasi</h1>
                <p>
                    Starter kit ini menyimpan data akun (nama, email, username) dan log aktivitas untuk keperluan
                    audit keamanan. Data tidak dijual ke pihak ketiga.
                </p>
                <p>
                    Anda dapat meminta ekspor data profil melalui menu Profil jika fitur ekspor diaktifkan pada
                    akun Anda.
                </p>
                <p>
                    Untuk penghapusan akun, gunakan fitur hapus akun di halaman Profil setelah konfirmasi
                    password.
                </p>
            </div>
        </GuestLayout>
    );
}
