import { FormEventHandler, useState } from 'react';
import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { PasswordInput } from '@/Components/ui/password-input';
import { Label } from '@/Components/ui/label';

export default function DeleteUserForm() {
    const [open, setOpen] = useState(false);
    const { data, setData, delete: destroy, processing, errors, reset } = useForm({
        password: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        destroy(route('profile.destroy'), {
            onSuccess: () => {
                setOpen(false);
                reset();
            },
        });
    };

    return (
        <div className="space-y-4">
            <p className="text-sm text-text-secondary">
                Setelah akun dihapus, semua data akan dihapus permanen.
            </p>
            {!open ? (
                <Button variant="destructive" onClick={() => setOpen(true)}>
                    Delete Account
                </Button>
            ) : (
                <form onSubmit={submit} className="space-y-3">
                    <div>
                        <Label>Password</Label>
                        <PasswordInput
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            autoFocus
                        />
                        <InputError message={errors.password} />
                    </div>
                    <div className="flex gap-2">
                        <Button type="button" variant="secondary" onClick={() => setOpen(false)}>
                            Batal
                        </Button>
                        <Button type="submit" variant="destructive" disabled={processing}>
                            Konfirmasi Hapus
                        </Button>
                    </div>
                </form>
            )}
        </div>
    );
}
