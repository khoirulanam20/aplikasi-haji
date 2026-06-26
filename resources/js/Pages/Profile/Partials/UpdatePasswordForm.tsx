import { FormEventHandler } from 'react';
import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';

export default function UpdatePasswordForm() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('password.update'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <p className="text-sm text-text-secondary">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.
            </p>
            <div>
                <Label>Current Password</Label>
                <PasswordInput
                    value={data.current_password}
                    onChange={(e) => setData('current_password', e.target.value)}
                    required
                />
                <InputError message={errors.current_password} />
            </div>
            <div>
                <Label>New Password</Label>
                <PasswordInput
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    required
                />
                <InputError message={errors.password} />
            </div>
            <div>
                <Label>Confirm Password</Label>
                <PasswordInput
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    required
                />
                <InputError message={errors.password_confirmation} />
            </div>
            <div className="flex justify-end">
                <Button type="submit" disabled={processing}>
                    Simpan
                </Button>
            </div>
        </form>
    );
}
