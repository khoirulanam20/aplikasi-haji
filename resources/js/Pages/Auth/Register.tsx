import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <h1 className="mb-2 text-center text-2xl font-bold">Create Account</h1>
            <p className="mb-8 text-center text-sm text-text-secondary">Sign up to get started.</p>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus />
                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required />
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
                    Register
                </Button>
                <div className="text-center text-sm">
                    Already have an account?{' '}
                    <Link href={route('login')} className="font-semibold text-primary">
                        Sign In
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
