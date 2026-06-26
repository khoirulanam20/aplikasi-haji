# ISMS Baseline — inertia_starterkit

Dokumen ini memetakan kontrol teknis starter kit terhadap tema ISO 27001 Annex A yang relevan untuk aplikasi web. **Bukan sertifikasi ISO** — kebijakan organisasi, risk register formal, dan bukti operasional harus dibuat terpisah oleh tim Anda.

## Risk Register (Template)

| ID | Risiko | Likelihood | Impact | Kontrol existing | Owner | Status |
|----|--------|------------|--------|------------------|-------|--------|
| R1 | Akun nonaktif masih login | Medium | High | `is_active` filter di login | DevOps | Mitigated |
| R2 | OAuth auto-provision | Medium | High | Registrasi flag + OAuth hardening | Dev | Mitigated |
| R3 | Tidak ada backup | High | High | `backup:database` command + schedule | Ops | Partial |
| R4 | Tidak ada MFA | Medium | High | TOTP optional (`TWO_FACTOR_ENABLED`) | Security | Partial |
| R5 | Tidak ada CI/CD | Low | Medium | Manual test gate di SECURITY.md | Dev | Accepted |

## Kontrol Teknis

### A.5 / A.8 — Access Control

- RBAC modul dinamis + middleware permission
- Impersonation dengan audit log (IP, user-agent)
- Optional 2FA TOTP
- Proteksi role `superadmin` dari sync permission kosong

### A.8.24 — Cryptography

- Password bcrypt, secret integrasi encrypted
- Session hardening via `.env.production.example`

### A.8.15 — Logging

- Spatie Activity Log (retensi 365 hari, `activitylog:clean` terjadwal)
- Impersonation log terpisah

### A.8.13 — Backup

- `php artisan backup:database` (SQLite copy / MySQL mysqldump)
- Terjadwal daily 02:00 via Laravel scheduler (`php artisan schedule:work`)

### A.8.25 — Secure SDLC

- PHPUnit feature tests (manual gate sebelum deploy)
- FormRequest untuk CRUD utama
- SECURITY.md untuk responsible disclosure

### A.5.34 — Privacy

- Halaman `/privacy`
- Cookie consent banner
- Ekspor data profil JSON (`/profile/export`)
- Hapus akun dengan konfirmasi password

## Monitoring (Opsional)

Set `SENTRY_LARAVEL_DSN` di `.env` untuk mengaktifkan integrasi Sentry jika package di-install di environment Anda.

## Tindakan Organisasi (Di Luar Repo)

- [ ] Kebijakan acceptable use
- [ ] Prosedur onboarding/offboarding
- [ ] Penetration test sebelum go-live
- [ ] Review akses berkala (quarterly)
