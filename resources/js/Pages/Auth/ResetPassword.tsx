import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.store'));
    };

    return (
        <GuestLayout>
            <Head title="Reset Password" />
            <h1 className="mb-8 text-center text-2xl font-bold">Reset Password</h1>
            <form onSubmit={submit} className="space-y-5">
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required autoFocus />
                    <InputError message={errors.email} className="mt-2" />
                </div>
                <div>
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput id="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required />
                    <InputError message={errors.password} className="mt-2" />
                </div>
                <div>
                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>
                <Button type="submit" className="w-full" disabled={processing}>
                    Reset Password
                </Button>
            </form>
        </GuestLayout>
    );
}
