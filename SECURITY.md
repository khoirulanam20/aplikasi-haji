# Security Policy

## Supported Versions

| Version | Supported |
| ------- | --------- |
| main    | yes       |

## Reporting a Vulnerability

Jika Anda menemukan kerentanan keamanan:

1. **Jangan** buka issue publik di repository.
2. Kirim detail ke maintainer proyek (email tim internal Anda).
3. Sertakan langkah reproduksi, dampak, dan versi commit jika memungkinkan.

Kami berusaha merespons dalam **72 jam** dan merilis perbaikan sesuai severity.

## Praktik Keamanan Starter Kit

- RBAC granular via Spatie Permission
- Rate limiting pada login, registrasi, forgot-password, dan 2FA challenge
- Security headers (HSTS di HTTPS, X-Frame-Options, dll.)
- Enkripsi secret integrasi di database
- Activity log + impersonation audit trail
- Optional TOTP 2FA (`TWO_FACTOR_ENABLED=true`)

## Checklist Deploy Production

1. Set `APP_DEBUG=false`, `REGISTRATION_ENABLED=false`
2. Set `SUPERADMIN_PASSWORD` kuat sebelum seed
3. Aktifkan `SESSION_SECURE_COOKIE=true` dan `SESSION_ENCRYPT=true` di HTTPS
4. Jalankan `php artisan config:cache` dan `php artisan route:cache`
5. Manual gate: `composer test`, `npm run build`, `composer audit`, `npm audit`

## Incident Response (Ringkas)

1. Kumpulkan log (`storage/logs`, activity log, impersonation log)
2. Isolasi akun terdampak (nonaktifkan user, revoke session)
3. Rotasi secret (`APP_KEY` hanya jika diperlukan — akan invalidate data terenkripsi)
4. Dokumentasikan timeline dan root cause

Lihat juga [docs/ISMS_BASELINE.md](docs/ISMS_BASELINE.md) untuk pemetaan kontrol ISO 27001.
