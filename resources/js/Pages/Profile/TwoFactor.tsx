import { FormEventHandler } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

interface TwoFactorProps {
    enabled: boolean;
    featureEnabled: boolean;
    provisioningUri: string | null;
}

export default function TwoFactor({ enabled, featureEnabled, provisioningUri }: TwoFactorProps) {
    const form = useForm({ code: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('profile.two-factor.confirm'));
    };

    if (!featureEnabled) {
        return (
            <AppLayout header="Two-Factor Authentication">
                <Head title="Two-Factor Authentication" />
                <div className="card text-sm text-text-secondary">
                    Fitur 2FA belum diaktifkan oleh administrator.
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout header="Two-Factor Authentication">
            <Head title="Two-Factor Authentication" />

            <div className="card max-w-lg space-y-4">
                {enabled ? (
                    <>
                        <p className="text-sm text-text-secondary">2FA aktif pada akun Anda.</p>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => router.delete(route('profile.two-factor.destroy'))}
                        >
                            Nonaktifkan 2FA
                        </Button>
                    </>
                ) : (
                    <>
                        <p className="text-sm text-text-secondary">
                            Aktifkan TOTP dengan aplikasi authenticator (Google Authenticator, Authy, dll.).
                        </p>
                        {!provisioningUri ? (
                            <Button type="button" onClick={() => router.post(route('profile.two-factor.enable'))}>
                                Mulai setup
                            </Button>
                        ) : (
                            <form onSubmit={submit} className="space-y-4">
                                <p className="break-all text-xs text-text-secondary">{provisioningUri}</p>
                                <div>
                                    <Label>Kode verifikasi</Label>
                                    <Input
                                        value={form.data.code}
                                        onChange={(e) => form.setData('code', e.target.value)}
                                        required
                                    />
                                    <InputError message={form.errors.code} />
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    Konfirmasi 2FA
                                </Button>
                            </form>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
