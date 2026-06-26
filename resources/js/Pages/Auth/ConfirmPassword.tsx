import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({ password: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Confirm Password" />
            <p className="mb-6 text-sm text-text-secondary">Please confirm your password before continuing.</p>
            <form onSubmit={submit} className="space-y-5">
                <div>
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput
                        id="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoFocus
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>
                <Button type="submit" disabled={processing}>
                    Confirm
                </Button>
            </form>
        </GuestLayout>
    );
}
