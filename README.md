# Inertia Starter Kit (L13)

Starter kit Laravel 13 dengan **Inertia.js + React + TypeScript + shadcn/ui**, mirror fitur dari [starter-kitl13](https://github.com/khoirulanam20/starter-kitl13) (versi Blade).

## Stack

- Laravel 13
- Inertia.js v2 + React 18 + TypeScript
- Tailwind CSS 3 + shadcn/ui (Radix primitives)
- Spatie Laravel Permission (RBAC modul dinamis)
- Laravel Socialite (Google OAuth)
- Intervention Image (upload WebP)

## Setup

```bash
composer install
npm install --legacy-peer-deps
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Jalankan development:

```bash
composer run dev
```

Atau terpisah:

```bash
php artisan serve
npm run dev
```

## Login Default

> **Peringatan keamanan:** Kredensial di bawah hanya untuk development. Di production, set `SUPERADMIN_PASSWORD` di `.env` sebelum `php artisan db:seed` — seeder akan gagal jika masih memakai password default `password`.

- Email: `superadmin@example.com` (atau `SUPERADMIN_EMAIL`)
- Password: `password` (atau `SUPERADMIN_PASSWORD`)

Lihat juga [`.env.production.example`](.env.production.example) untuk baseline hardening production.

## Admin Panel

- URL prefix: `/app` (konfigurasi via `ADMIN_ROUTE_PREFIX`)
- Dashboard: `/app/dashboard`

## Fitur

- Auth: login email/username, register, reset password, Google OAuth
- RBAC modul dinamis dengan permission `{code}.{action}`
- Manajemen: Users, Roles & Permissions, Modules, Master Data, Web Settings
- Impersonate user
- Profile dengan upload avatar (WebP)
- Dynamic theme dari web settings
- Notifikasi database

## Perbedaan vs starter-kitl13 (Blade)

| Aspek | starter-kitl13 | inertia_starterkit |
|-------|------------------|-------------------|
| UI | Blade + Alpine.js | Inertia + React |
| Components | Blade components | shadcn/ui |
| Icons | Iconoir CDN | Lucide React (+ HTML icon modul) |

## Struktur Frontend

```
resources/js/
├── Layouts/          # AppLayout, GuestLayout
├── Pages/
│   ├── Auth/
│   ├── Admin/
│   ├── Profile/
│   └── Notifications/
├── Components/ui/    # shadcn primitives
└── hooks/            # usePermission
```

## Testing

```bash
php artisan test
```
