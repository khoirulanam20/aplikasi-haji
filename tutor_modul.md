# Tutorial: Membuat Modul Baru (Starter Kit)

Panduan langkah demi langkah untuk menambah modul admin baru dengan RBAC, sidebar, dan permission yang konsisten.

---

## Daftar isi

1. [Konsep singkat](#1-konsep-singkat)
2. [Jenis modul (layout)](#2-jenis-modul-layout)
3. [Kamus permission](#3-kamus-permission)
4. [Aturan penamaan](#4-aturan-penamaan)
5. [Alur kerja (urutan yang benar)](#5-alur-kerja-urutan-yang-benar)
6. [Langkah 1 — Rencana modul](#6-langkah-1--rencana-modul)
7. [Langkah 2 — Controller & view](#7-langkah-2--controller--view)
8. [Langkah 3 — Route & middleware](#8-langkah-3--route--middleware)
9. [Langkah 4 — Proteksi di Blade](#9-langkah-4--proteksi-di-blade)
10. [Langkah 5 — Daftar modul di UI](#10-langkah-5--daftar-modul-di-ui)
11. [Langkah 6 — Seeder (opsional)](#11-langkah-6--seeder-opsional)
12. [Langkah 7 — Assign role & cache](#12-langkah-7--assign-role--cache)
13. [Langkah 8 — Verifikasi](#13-langkah-8--verifikasi)
14. [Contoh lengkap: modul Laporan](#14-contoh-lengkap-modul-laporan)
15. [Kesalahan umum](#15-kesalahan-umum)
16. [Referensi file penting](#16-referensi-file-penting)

---

## 1. Konsep singkat

Starter kit ini memisahkan **fitur aplikasi** (controller, route, view) dari **metadata modul** (tabel `modules`, permission Spatie, menu sidebar).

| Lapisan | Fungsi |
|--------|--------|
| **Route + Controller** | Halaman benar-benar bisa diakses |
| **Modul (UI Modules)** | Membuat permission `{kode}.{aksi}` & menu sidebar |
| **Role & Permission** | Menentukan role mana yang boleh akses |

Permission selalu mengikuti pola:

```text
{kode_modul}.{aksi}
```

Contoh: `laporan.list`, `websetting.update`.

---

## 2. Jenis modul (layout)

Saat **Modules → Tambah Modul**, pilih jenis modul. Preset aksi RBAC otomatis terisi; bisa disesuaikan manual setelahnya.

| Jenis | Kode `layout_type` | Kapan dipakai | Preset aksi RBAC |
|-------|-------------------|---------------|------------------|
| **Table base** | `table_base` | Halaman daftar + tombol tambah + aksi show/edit/hapus per baris (contoh: Pengguna) | `list`, `show`, `create`, `edit`, `update`, `delete` |
| **Form base** | `form_base` | Satu halaman form + tombol simpan saja (contoh: Web Setting) | `list`, `read`, `create`, `update` |

### Aturan form base

| Aksi user | Permission yang dibutuhkan |
|-----------|---------------------------|
| Buka halaman | `list` **atau** `read` |
| Simpan perubahan | **`create` dan `update` bersamaan** |
| Hanya lihat (tanpa simpan) | `list` saja, atau `read` saja, tanpa `create`/`update` |

**Route (Web Setting / form base):**

```php
Route::get('/web-setting', ...)
    ->middleware('permission.any:websetting.list,websetting.read');

Route::put('/web-setting', ...)
    ->middleware('permission.all:websetting.create,websetting.update');
```

**Blade:** gunakan `FormModuleAccess` atau variabel `$canSave` dari controller; tombol simpan hanya jika `create` + `update`.

### Aturan table base

| UI | Permission tipikal |
|----|-------------------|
| Buka halaman / sidebar | `list` |
| Tombol tambah | `create` |
| Tombol edit baris | `update` (atau `edit` jika dipakai di Blade) |
| Tombol hapus | `delete` |
| Detail/show | `show` |

Contoh referensi: [`users/index.blade.php`](resources/views/pageadmin/users/index.blade.php).

---

## 3. Kamus permission

Penjelasan tiap aksi dalam bahasa umum (`App\Models\Module::ACTIONS`):

| Aksi | Arti umum | Table base | Form base |
|------|-----------|------------|-----------|
| `read` | Melihat isi data tanpa mengubah | Opsional (mis. detail) | Buka halaman / lihat form; **tidak cukup** untuk simpan |
| `list` | Membuka halaman daftar atau menu sidebar | Wajib untuk halaman index | Buka halaman; **tidak cukup** untuk simpan |
| `show` | Melihat satu record (halaman detail) | Tombol/detail | Jarang dipakai |
| `create` | Membuat data baru (CRUD) | Tombol **Tambah Data** / buat record | Bagian izin **simpan** (harus ada bersama `update`) |
| *(kustom)* | Judul bebas via tombol **+** di form modul | `@can('{kode}.{slug}')` — slug dari judul (mis. `export_excel`) | Opsional |
| `edit` | Mengubah lewat form edit terpisah | Tombol edit (jika Blade pakai `@can('*.edit')`) | Opsional |
| `update` | Menyimpan perubahan ke server (PUT/PATCH) | Route update / simpan edit | Bagian izin **simpan** (harus ada bersama `create`) |
| `delete` | Menghapus data | Tombol hapus | Tidak dipakai |
| `notify` | Notifikasi aktivitas CRUD modul | Hanya jika **Fitur notifikasi** aktif di form modul; `@can('{kode}.notify')` + `ModuleNotification::sendIfAllowed()` | Opsional |
| `assign` | Menetapkan hak ke entitas lain (mis. permission ke role) | Modul Role Permission | Tidak dipakai |

> **Notifikasi per modul:** centang **Notifikasi** (`notify`) di daftar RBAC modul. Permission `{kode}.notify` terpisah dari permission sistem `notifications.list` / `notifications.read` (menu bell).

> **Form base:** centang `list` + `read` + `create` + `update` di modul; role yang hanya boleh lihat cukup `list` atau `read` saja.

### Permission sistem (bukan modul di tabel `modules`)

Muncul di grup **Permission Sistem** di Role Permission:

| Prefix | Arti | Lihat halaman | Ubah / simpan |
|--------|------|---------------|---------------|
| `profile` | Profil pengguna login | `profile.read` | `profile.update` |
| `notifications` | Daftar notifikasi | `notifications.list` | Tandai dibaca: `notifications.read` |
| `module-groups` | Grup modul (UI Modules) | — | `create`, `update`, `delete` |
| `impersonate` | Login sebagai user lain | — | `start`, `stop` |

### Impersonate dari menu Pengguna

1. Centang **`impersonate.start`** (dan disarankan `impersonate.stop`) pada role di **Role Permission → Permission Sistem → Impersonate**.
2. Buka **Pengguna** — di kolom **Aksi** muncul tombol impersonate (ikon log-in) untuk user **aktif** selain diri sendiri dan bukan role superadmin.
3. Konfirmasi modal → sistem login sebagai user target dan redirect ke dashboard.
4. Keluar lewat banner kuning **Exit Impersonation** di atas konten admin.

**Batasan:** tidak bisa impersonate akun sendiri, user nonaktif, atau user dengan role `superadmin`.

> **Profile** bukan form base modul: cukup `read` + `update` (bukan `create` + `update`). Route `/profile` sudah memakai middleware; tanpa `profile.update` tombol Save disembunyikan.

---

## 4. Aturan penamaan

| Item | Format | Contoh benar | Contoh salah |
|------|--------|--------------|--------------|
| Kode modul | `a-z`, `0-9`, `_` saja | `laporan`, `websetting` | `laporan-baru`, `master-data` |
| URL path | kebab-case (bebas) | `/app/laporan` | — |
| Route name | `{prefix}.{slug}.{aksi}` | `app.laporan.index` | `/app/laporan` |
| Permission | `{kode}.{aksi}` | `laporan.list` | `laporan-list` |

**Prefix admin** (dari `config/starterkit.php`):

- URL: `ADMIN_ROUTE_PREFIX` → default `app` → `/app/...`
- Route name: `ADMIN_ROUTE_NAME` → default `app` → `app.laporan.index`

Helper di project:

```php
admin_route_name('laporan.index');  // → app.laporan.index
admin_url('laporan');               // → /app/laporan
route(admin_route_name('laporan.index'));
```

**Aksi RBAC yang tersedia** (`App\Models\Module::ACTIONS`):

`read`, `list`, `delete`, `assign`, `show`, `create`, `edit`, `update`, `notify` (+ hak akses kustom per modul; `notify` hanya jika fitur notifikasi modul aktif)

> **Best practice:** Pilih jenis modul (table base / form base) agar preset sesuai. Sesuaikan centang aksi jika route/view tidak memakai semua permission.

---

## 5. Alur kerja (urutan yang benar)

```text
Rencana → Controller + View → Route (permission.module) → Cek route:list
    → Daftar modul (UI / Seeder) → Assign role → Cache reset → Test
```

**Jangan** mendaftarkan modul di sidebar sebelum route Laravel-nya ada — form modul akan menolak `route_name` yang belum terdaftar.

---

## 6. Langkah 1 — Rencana modul

Isi template ini sebelum coding:

| Field | Contoh table base | Contoh form base |
|-------|-------------------|------------------|
| Judul UI | Laporan | Web Setting |
| Kode modul | `laporan` | `websetting` |
| **Jenis modul** | `table_base` | `form_base` |
| URL | `/app/laporan` | `/app/web-setting` |
| Route name utama | `app.laporan.index` | `app.web-setting.index` |
| Aksi RBAC (preset) | `list`, `show`, `create`, `edit`, `update`, `delete` | `list`, `read`, `create`, `update` |
| Grup sidebar | Data | Pengaturan Website |

---

## 7. Langkah 2 — Controller & view

### Buat controller

```bash
php artisan make:controller Superadmin/LaporanController
```

Contoh `app/Http/Controllers/Superadmin/LaporanController.php`:

```php
<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        return view('pageadmin.laporan.index');
    }
}
```

### Buat view

Path: `resources/views/pageadmin/laporan/index.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">Laporan</x-slot>

    <div class="card">
        <p class="text-sm text-text-secondary">Konten modul di sini.</p>
    </div>
</x-app-layout>
```

Ikuti pola modul existing: `master_data`, `web_setting`.

---

## 8. Langkah 3 — Route & middleware

### Import controller

Di `routes/web.php`, tambahkan `use` di bagian atas:

```php
use App\Http\Controllers\Superadmin\LaporanController;
```

### Daftarkan route (dalam grup prefix `app`)

```php
Route::get('/laporan', [LaporanController::class, 'index'])
    ->middleware('permission.module:laporan.index,list')
    ->name('laporan.index');
```

Untuk aksi lain:

```php
Route::post('/laporan', [LaporanController::class, 'store'])
    ->middleware('permission.module:laporan.index,create')
    ->name('laporan.store');

Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])
    ->middleware('permission.module:laporan.index,update')
    ->name('laporan.update');

Route::delete('/laporan/{laporan}', [LaporanController::class, 'destroy'])
    ->middleware('permission.module:laporan.index,delete')
    ->name('laporan.destroy');
```

### Middleware `permission.module` (disarankan)

Format: `permission.module:{route-suffix},{aksi}`

- `laporan.index` → di-resolve menjadi `app.laporan.index`
- Permission dicek dari **kode modul di database** (via `ModulePermissionResolver`)
- Kesalahan kode/route ditolak saat simpan modul (lihat **Konvensi modul** di bawah)

Alternatif lama (masih valid jika kode modul sudah pasti):

```php
->middleware('permission.check:laporan.list')
```

### Verifikasi route

```bash
php artisan route:list --name=laporan
```

Pastikan nama route = `app.laporan.index` (atau sesuai `ADMIN_ROUTE_NAME`).

---

## 9. Langkah 4 — Proteksi di Blade

Gunakan `@can` dengan nama permission penuh:

```blade
@can('laporan.create')
    <button type="button" class="btn-primary">Tambah</button>
@endcan

@can('laporan.update')
    {{-- form edit --}}
@endcan

@can('laporan.delete')
    {{-- tombol hapus --}}
@endcan
```

Form action:

```blade
<form method="POST" action="{{ route(admin_route_name('laporan.store')) }}">
    @csrf
</form>
```

### Modul dengan banyak tingkat akses (opsional)

Di controller, bisa pakai `ModulePermissionResolver` seperti modul lain:

```php
use App\Support\Modules\ModulePermissionResolver;

public function __construct(private readonly ModulePermissionResolver $permissions) {}

// Di index():
$routeName = admin_route_name('laporan.index');
$canUpdate = collect($this->permissions->aliasesForRoute($routeName, 'update'))
    ->contains(fn ($p) => auth()->user()?->can($p));
```

Lalu di view: `@if ($canUpdate)` untuk tombol simpan.

### Form base (tombol simpan)

Controller:

```php
use App\Support\Modules\FormModuleAccess;

public function index(FormModuleAccess $formAccess): View
{
    return view('pageadmin.web_setting.index', [
        'canSave' => $formAccess->canSave('websetting', auth()->user()),
    ]);
}
```

View: tampilkan tombol simpan hanya jika `$canSave`; nonaktifkan field jika tidak bisa simpan (lihat `web_setting/index.blade.php`).

---

## 10. Langkah 5 — Daftar modul di UI

1. Login sebagai user dengan akses **Modules** (`modules.list`, `modules.create`).
2. Buka **Modules → Tambah Modul**.
3. Isi form:

| Field | Nilai contoh |
|-------|----------------|
| Grup modul | Data |
| Judul modul | Laporan |
| **Jenis modul** | Table base atau Form base (preset aksi otomatis) |
| **Kode modul** | `laporan` |
| **Route name** | `app.laporan.index` |
| Icon | `<i class="iconoir-reports"></i>` |
| Tampil di sidebar | ✓ (jika ingin di menu) |
| Aktif | ✓ |
| Hak akses (RBAC) | Ikuti preset jenis modul; sesuaikan jika perlu |

4. **Simpan**.

Sistem otomatis:

- Membuat permission di tabel `permissions`
- Memberi permission baru ke role **superadmin**
- Membuat/update menu sidebar (jika sidebar aktif + route valid)

---

## 11. Langkah 6 — Seeder (opsional)

Untuk environment baru / tim, tambahkan definisi di `database/seeders/ModuleSeeder.php`:

```php
[
    'title' => 'Laporan',
    'code' => 'laporan',
    'route_name' => 'app.laporan.index',
    'icon' => '<i class="iconoir-reports"></i>',
    'sort_order' => 80,
    'layout_type' => 'table_base', // atau 'form_base'
    'actions' => ['list', 'create', 'update', 'delete'],
],
```

Form base contoh Web Setting:

```php
'layout_type' => 'form_base',
'actions' => ['list', 'read', 'create', 'update'],
```

Form base **Config** (Google OAuth, SMTP, AI API):

```php
'title' => 'Config',
'code' => 'config',
'layout_type' => 'form_base',
'route_name' => 'app.config.index',
'actions' => ['list', 'read', 'create', 'update'],
```

- Data disimpan di tabel `app_configs` (singleton `id = 1`); secret field dienkripsi.
- **Prioritas runtime:** nilai DB override `.env`; jika kolom DB kosong, fallback ke `.env` (`GOOGLE_*`, `MAIL_*`, `AI_*`).
- Konsumsi di kode: `config('services.google.*')`, `config('mail.*')`, `config('ai.*')` setelah `IntegrationConfigService::apply()` di boot.
- Field secret kosong saat save = pertahankan nilai lama.

Letakkan di dalam array `modules` grup yang sesuai, lalu:

```bash
php artisan db:seed --class=ModuleSeeder
php artisan permission:cache-reset
```

---

## 12. Langkah 7 — Assign role & cache

1. Buka **Role & Permission**.
2. Klik ikon shield pada role target (mis. Operator).
3. Centang permission `laporan.*` yang diperlukan.
4. **Simpan** — harus muncul: *"Permission role berhasil disimpan."*
5. Jalankan:

```bash
php artisan permission:cache-reset
```

6. User yang sudah login: **logout → login** lagi.

> Setiap perubahan permission role, cache Spatie harus di-reset agar `can()` langsung akurat.

---

## 13. Langkah 8 — Verifikasi

Checklist:

- [ ] `php artisan route:list --name=laporan` — route ada
- [ ] User **dengan** `laporan.list` → halaman OK (200)
- [ ] User **tanpa** `laporan.list` → 403 Forbidden
- [ ] Menu sidebar muncul jika: modul aktif + sidebar on + user punya `laporan.list`
- [ ] Tombol create/update/delete hanya tampil sesuai `@can`
- [ ] Setelah assign role, permission benar-benar tersimpan (buka ulang modal role)

### Test otomatis (disarankan)

Buat file mirip `tests/Feature/WebSetting/WebSettingAccessTest.php`:

```php
Permission::create(['name' => 'laporan.list', 'guard_name' => 'web']);
$user->givePermissionTo('laporan.list');
$this->actingAs($user)->get('/app/laporan')->assertOk();
```

Jalankan:

```bash
php artisan test --filter=Laporan
```

---

## 14. Contoh lengkap: modul Laporan

### Ringkasan file

| File | Tindakan |
|------|----------|
| `app/Http/Controllers/Superadmin/LaporanController.php` | Buat |
| `resources/views/pageadmin/laporan/index.blade.php` | Buat |
| `routes/web.php` | Tambah route + middleware |
| UI **Modules** atau `ModuleSeeder.php` | Daftar modul `laporan` |
| **Role & Permission** | Assign ke role |

### Mapping permission ↔ route

| Route | Middleware | Permission |
|-------|------------|------------|
| GET `/app/laporan` | `permission.module:laporan.index,list` | `laporan.list` |
| POST `/app/laporan` | `permission.module:laporan.index,create` | `laporan.create` |
| PUT `/app/laporan/{id}` | `permission.module:laporan.index,update` | `laporan.update` |
| DELETE `/app/laporan/{id}` | `permission.module:laporan.index,delete` | `laporan.delete` |

---

## 15. Kesalahan umum

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| Validasi gagal saat simpan modul | Route name belum ada | Buat route dulu, cek `route:list` |
| 403 meskipun permission dicentang | Kode modul tidak sama dengan permission di route | Samakan kode modul, permission, dan middleware |
| Kode modul ditolak | Pakai strip `-` | Ganti underscore: `config_api` |
| Perubahan role tidak terasa | Cache Spatie | `permission:cache-reset` + login ulang |
| Menu tidak muncul | Sidebar off / modul nonaktif / tanpa `list` | Cek modul + permission `*.list` |
| Terlalu banyak checkbox permission | Semua aksi dicentang di modul | Centang hanya yang dipakai |

### Konvensi modul (`module_conventions`)

Tambahkan aturan di [`config/starterkit.php`](config/starterkit.php) jika modul tertentu punya kode/route wajib:

```php
'module_conventions' => [
    'deprecated_codes' => [
        'kode_lama' => 'Pesan error untuk kode yang tidak boleh dipakai.',
    ],
    'routes' => [
        'app.contoh.index' => 'kode_modul',
    ],
],
```

Validator memanggil `ModuleConventionValidator` saat simpan modul di UI **Modules**.

### Hapus modul dari database

**Modul masih ada di tabel `modules`:**

```bash
php artisan starterkit:purge-module kode_modul --force
php artisan permission:cache-reset
```

**Permission masih muncul di Role Permission (orphan) — modul sudah dihapus dari kode/DB tetapi baris `permissions` masih ada:**

```bash
php artisan starterkit:purge-permission-prefix kode_modul --force
php artisan permission:cache-reset
```

Contoh prefix legacy yang pernah dipakai: `config_api`, `api_config`.

---

## 16. Referensi file penting

| File | Peran |
|------|-------|
| `routes/web.php` | Route admin + middleware |
| `app/Http/Controllers/Superadmin/ModuleController.php` | CRUD metadata modul |
| `app/Support/Modules/ModuleRegistryService.php` | Sync permission & menu |
| `app/Support/Modules/ModulePermissionResolver.php` | Resolve permission dari route + kode modul |
| `app/Support/Modules/ModuleConventionValidator.php` | Validasi kode/route modul (alert jika salah) |
| `app/Support/Modules/ModuleConventions.php` | Baca konvensi dari config |
| `app/Http/Middleware/EnsureModulePermission.php` | Middleware `permission.module` |
| `app/Http/Middleware/EnsurePermission.php` | Middleware `permission.check` |
| `app/Http/Middleware/EnsureAllPermissions.php` | Middleware `permission.all` (semua permission wajib) |
| `app/Http/Middleware/EnsureAnyPermission.php` | Middleware `permission.any` (salah satu permission) |
| `app/Support/Modules/FormModuleAccess.php` | Helper akses lihat/simpan form base |
| `database/seeders/ModuleSeeder.php` | Modul default + permission superadmin |
| `config/starterkit.php` | Prefix URL & route name admin |
| `app/helpers.php` | `admin_route_name()`, `admin_url()` |

### Modul contoh di codebase

- **CRUD:** `MasterDataController`, `master_data/index.blade.php`
- **Settings tunggal:** `WebSettingController`, `web_setting/index.blade.php`

---

## Cheat sheet perintah

```bash
# Cek route
php artisan route:list --name=nama-modul

# Seed modul
php artisan db:seed --class=ModuleSeeder

# Pindahkan modul ke grup lain
php artisan starterkit:relocate-module kode_modul pengaturan_website

# Hapus modul + permission (record modul harus ada)
php artisan starterkit:purge-module kode_modul --force

# Hapus permission orphan by prefix (tanpa record modul)
php artisan starterkit:purge-permission-prefix kode_modul --force

# Reset cache permission
php artisan permission:cache-reset

# Test
php artisan test --filter=NamaModul
```

---

*Dokumen ini mengikuti konvensi starter kit per Mei 2026. Jika menambah middleware atau mengubah alur modul, update file ini agar tim tetap selaras.*
