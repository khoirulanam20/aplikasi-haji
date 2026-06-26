import { FormEventHandler } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';
import { PageProps } from '@/types';

export default function Login({
    canResetPassword,
    canRegister,
    status,
}: {
    canResetPassword: boolean;
    canRegister: boolean;
    status?: string;
}) {
    const { app } = usePage<PageProps>().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        login: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Login" />

            <h1 className="mb-2 text-center text-2xl font-bold text-text-primary">Welcome Back</h1>
            <p className="mb-8 text-center text-sm text-text-secondary">
                Please enter your details to sign in.
            </p>

            {status && <p className="mb-4 text-sm text-success">{status}</p>}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <Label htmlFor="login">Username or Email</Label>
                    <Input
                        id="login"
                        name="login"
                        value={data.login}
                        autoComplete="username"
                        autoFocus
                        placeholder="Enter your username or email"
                        onChange={(e) => setData('login', e.target.value)}
                    />
                    <InputError message={errors.login} className="mt-2" />
                </div>

                <div>
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        value={data.password}
                        autoComplete="current-password"
                        placeholder="••••••••"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex cursor-pointer items-center gap-2">
                        <Checkbox
                            checked={data.remember}
                            onCheckedChange={(checked) => setData('remember', checked === true)}
                        />
                        <span className="text-sm font-medium text-text-secondary">Remember me</span>
                    </label>
                    {canResetPassword && (
                        <Link href={route('password.request')} className="text-sm font-semibold text-primary hover:text-primary-hover">
                            Forgot password?
                        </Link>
                    )}
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    Sign In
                </Button>

                {app.googleOAuthEnabled && (
                    <>
                        <div className="relative flex items-center py-2">
                            <div className="flex-grow border-t border-border" />
                            <span className="mx-4 flex-shrink-0 text-xs font-medium uppercase tracking-wider text-text-secondary">
                                Or continue with
                            </span>
                            <div className="flex-grow border-t border-border" />
                        </div>

                        <Button variant="secondary" className="w-full" asChild>
                            <a href={route('auth.google.redirect')}>Google</a>
                        </Button>
                    </>
                )}

                {canRegister && (
                    <div className="mt-6 text-center text-sm font-medium text-text-secondary">
                        Don&apos;t have an account yet?{' '}
                        <Link href={route('register')} className="font-semibold text-primary hover:text-primary-hover">
                            Sign Up
                        </Link>
                    </div>
                )}
            </form>
        </GuestLayout>
    );
}
