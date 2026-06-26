import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Button } from '@/Components/ui/button';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Email Verification" />
            <p className="mb-4 text-sm text-text-secondary">
                Thanks for signing up! Please verify your email by clicking the link we sent.
            </p>
            {status === 'verification-link-sent' && (
                <p className="mb-4 text-sm text-success">A new verification link has been sent.</p>
            )}
            <form onSubmit={submit} className="space-y-4">
                <Button type="submit" disabled={processing}>
                    Resend Verification Email
                </Button>
                <Link href={route('logout')} method="post" as="button" className="text-sm text-primary">
                    Log Out
                </Link>
            </form>
        </GuestLayout>
    );
}
