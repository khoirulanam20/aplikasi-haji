import type { Alignment, Side } from 'driver.js';

export interface TourStepDef {
    element: string;
    title: string;
    description: string;
    side?: Side;
    align?: Alignment;
}

export const FALLBACK_TOUR_STEPS: TourStepDef[] = [
    {
        element: '[data-tour="page-header"]',
        title: 'Judul Halaman',
        description: 'Area ini menampilkan nama halaman atau modul yang sedang Anda buka.',
        side: 'bottom',
    },
    {
        element: '[data-tour="page-content"]',
        title: 'Konten Utama',
        description: 'Semua fitur dan data halaman ini ditampilkan di area konten utama.',
        side: 'top',
    },
];

export const TOUR_REGISTRY: Record<string, TourStepDef[]> = {
    'app.dashboard': [
        {
            element: '[data-tour="dashboard-welcome"]',
            title: 'Selamat Datang',
            description: 'Ringkasan singkat aplikasi dan informasi versi Laravel/PHP.',
            side: 'bottom',
        },
        {
            element: '[data-tour="dashboard-stats"]',
            title: 'Statistik Overview',
            description: 'Lihat jumlah pengguna, role, dan master data dalam satu pandangan.',
            side: 'bottom',
        },
        {
            element: '[data-tour="dashboard-charts"]',
            title: 'Grafik Aktivitas',
            description: 'Area grafik untuk memantau tren aktivitas dan pertumbuhan data.',
            side: 'top',
        },
        {
            element: '[data-tour="dashboard-activity"]',
            title: 'Aktivitas Terbaru',
            description: 'Daftar aktivitas pengguna terbaru. Gunakan tab untuk memfilter status.',
            side: 'top',
        },
    ],
    'app.users.index': [
        {
            element: '[data-tour="users-search"]',
            title: 'Pencarian',
            description: 'Cari pengguna berdasarkan nama, username, atau email.',
            side: 'bottom',
        },
        {
            element: '[data-tour="users-create"]',
            title: 'Tambah Pengguna',
            description: 'Buat akun baru dan tetapkan role untuk pengguna.',
            side: 'left',
        },
        {
            element: '[data-tour="users-table"]',
            title: 'Tabel Pengguna',
            description: 'Kelola status, edit, impersonate, atau hapus pengguna dari sini.',
            side: 'top',
        },
    ],
    'app.role-permission.index': [
        {
            element: '[data-tour="roles-toolbar"]',
            title: 'Kelola Role',
            description: 'Tambah role baru untuk mengatur hak akses pengguna.',
            side: 'bottom',
        },
        {
            element: '[data-tour="roles-table"]',
            title: 'Daftar Role',
            description: 'Lihat semua role. Gunakan ikon perisai untuk mengatur permission per role.',
            side: 'top',
        },
    ],
    'app.modules.index': [
        {
            element: '[data-tour="modules-toolbar"]',
            title: 'Manajemen Modul',
            description: 'Tambah grup modul atau modul baru. Baca panduan modul untuk referensi lengkap.',
            side: 'bottom',
        },
        {
            element: '[data-tour="modules-list"]',
            title: 'Daftar Modul',
            description: 'Seret ikon grip untuk mengubah urutan grup dan modul di sidebar.',
            side: 'top',
        },
    ],
    'app.modules.show': [
        {
            element: '[data-tour="module-detail"]',
            title: 'Detail Modul',
            description: 'Informasi lengkap modul: grup, layout, route, dan status aktif.',
            side: 'bottom',
        },
        {
            element: '[data-tour="module-actions"]',
            title: 'Aksi RBAC',
            description: 'Daftar aksi permission yang diaktifkan untuk modul ini.',
            side: 'top',
        },
    ],
    'app.master-data.index': [
        {
            element: '[data-tour="masterdata-search"]',
            title: 'Pencarian',
            description: 'Cari data master berdasarkan nama.',
            side: 'bottom',
        },
        {
            element: '[data-tour="masterdata-create"]',
            title: 'Tambah Data',
            description: 'Buat entri master data baru.',
            side: 'left',
        },
        {
            element: '[data-tour="masterdata-table"]',
            title: 'Tabel Master Data',
            description: 'Kelola, edit, atau hapus data master dari tabel ini.',
            side: 'top',
        },
    ],
    'app.web-setting.index': [
        {
            element: '[data-tour="websetting-identity"]',
            title: 'Identitas Aplikasi',
            description: 'Atur nama aplikasi, tagline, dan deskripsi yang tampil di UI.',
            side: 'bottom',
        },
        {
            element: '[data-tour="websetting-theme"]',
            title: 'Warna Tema',
            description: 'Sesuaikan warna primary dan secondary untuk seluruh aplikasi.',
            side: 'top',
        },
        {
            element: '[data-tour="websetting-branding"]',
            title: 'Logo & Favicon',
            description: 'Unggah logo dan favicon untuk branding aplikasi.',
            side: 'top',
        },
    ],
    'app.config.index': [
        {
            element: '[data-tour="config-tabs"]',
            title: 'Tab Integrasi',
            description: 'Pilih tab untuk mengatur Google OAuth, email SMTP, atau AI API.',
            side: 'bottom',
        },
        {
            element: '[data-tour="config-form"]',
            title: 'Form Konfigurasi',
            description: 'Isi kredensial integrasi. Kosongkan field secret jika tidak ingin mengubahnya.',
            side: 'top',
        },
    ],
    'app.activity-log.index': [
        {
            element: '[data-tour="activitylog-filters"]',
            title: 'Filter Log',
            description: 'Saring log berdasarkan kata kunci, pengguna, event, jenis data, dan rentang tanggal.',
            side: 'bottom',
        },
        {
            element: '[data-tour="activitylog-table"]',
            title: 'Tabel Log',
            description: 'Lihat riwayat aktivitas. Klik baris untuk detail perubahan data.',
            side: 'top',
        },
    ],
    'profile.edit': [
        {
            element: '[data-tour="profile-tabs"]',
            title: 'Tab Profil',
            description: 'Kelola profil, ubah password, atau hapus akun dari tab ini.',
            side: 'bottom',
        },
        {
            element: '[data-tour="profile-form"]',
            title: 'Form Profil',
            description: 'Perbarui nama, username, email, dan foto profil Anda.',
            side: 'top',
        },
    ],
    'notifications.index': [
        {
            element: '[data-tour="notifications-list"]',
            title: 'Daftar Notifikasi',
            description: 'Semua notifikasi Anda ditampilkan di sini secara kronologis.',
            side: 'bottom',
        },
        {
            element: '[data-tour="notifications-actions"]',
            title: 'Tandai Dibaca',
            description: 'Klik tombol untuk menandai notifikasi sebagai sudah dibaca.',
            side: 'left',
        },
    ],
};

export function getTourStepsForRoute(routeName: string | null | undefined): TourStepDef[] {
    if (!routeName) {
        return [];
    }

    return TOUR_REGISTRY[routeName] ?? FALLBACK_TOUR_STEPS;
}
