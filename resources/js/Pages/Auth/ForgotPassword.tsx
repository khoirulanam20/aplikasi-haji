import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot Password" />
            <h1 className="mb-2 text-center text-2xl font-bold">Forgot Password</h1>
            <p className="mb-8 text-center text-sm text-text-secondary">
                Enter your email and we will send you a reset link.
            </p>
            {status && <p className="mb-4 text-sm text-success">{status}</p>}
            <form onSubmit={submit} className="space-y-5">
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required autoFocus />
                    <InputError message={errors.email} className="mt-2" />
                </div>
                <Button type="submit" className="w-full" disabled={processing}>
                    Email Password Reset Link
                </Button>
            </form>
        </GuestLayout>
    );
}
