import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

export default function TwoFactorChallenge() {
    const form = useForm({ code: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('two-factor.verify'));
    };

    return (
        <GuestLayout>
            <Head title="Verifikasi Dua Faktor" />

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <h2 className="text-lg font-semibold text-text-primary">Verifikasi dua faktor</h2>
                    <p className="mt-1 text-sm text-text-secondary">
                        Masukkan kode 6 digit dari aplikasi authenticator Anda.
                    </p>
                </div>
                <div>
                    <Label htmlFor="code">Kode</Label>
                    <Input
                        id="code"
                        inputMode="numeric"
                        autoComplete="one-time-code"
                        value={form.data.code}
                        onChange={(e) => form.setData('code', e.target.value)}
                        required
                    />
                    <InputError message={form.errors.code} />
                </div>
                <Button type="submit" className="w-full" disabled={form.processing}>
                    Verifikasi
                </Button>
            </form>
        </GuestLayout>
    );
}
