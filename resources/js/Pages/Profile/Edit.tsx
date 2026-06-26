import { FormEventHandler, useEffect, useMemo } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm';
import InputError from '@/Components/InputError';
import UserAvatar from '@/Components/UserAvatar';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { User } from '@/types';

interface ProfileEditProps {
    user: User;
    canUpdateProfile: boolean;
    mustVerifyEmail: boolean;
    status?: string;
}

export default function Edit({ user, canUpdateProfile, mustVerifyEmail, status: _status }: ProfileEditProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: user.name,
        username: user.username ?? '',
        email: user.email,
        avatar: null as File | null,
        remove_avatar: false,
    });

    const avatarObjectPreview = useMemo(
        () => (data.avatar ? URL.createObjectURL(data.avatar) : null),
        [data.avatar],
    );

    useEffect(() => {
        return () => {
            if (avatarObjectPreview) {
                URL.revokeObjectURL(avatarObjectPreview);
            }
        };
    }, [avatarObjectPreview]);

    const avatarPreview = avatarObjectPreview ?? user.avatar_url;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        const hasAvatarFile = data.avatar instanceof File;

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setData('avatar', null);
            },
        };

        if (hasAvatarFile) {
            router.post(
                route('profile.update'),
                {
                    _method: 'patch',
                    name: data.name,
                    username: data.username,
                    email: data.email,
                    avatar: data.avatar,
                    ...(data.remove_avatar ? { remove_avatar: true } : {}),
                },
                { ...options, forceFormData: true },
            );

            return;
        }

        patch(route('profile.update'), options);
    };

    return (
        <AppLayout header="Profile Settings">
            <Head title="Profile Settings" />

            {!canUpdateProfile && (
                <div className="card mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Anda hanya memiliki akses lihat profil. Hubungi admin untuk mengubah data.
                </div>
            )}

            {/* {status === 'profile-updated' && (
                <p className="mb-4 text-sm text-success">Profil berhasil diperbarui.</p>
            )} */}

            <div className="card">
                <Tabs defaultValue="profile" data-tour="profile-tabs">
                    <TabsList>
                        <TabsTrigger value="profile">
                            <IconoirIcon name="user" className="text-base" />
                            Profil
                        </TabsTrigger>
                        {canUpdateProfile && (
                            <>
                                <TabsTrigger value="password">
                                    <IconoirIcon name="lock" className="text-base" />
                                    Password
                                </TabsTrigger>
                                <TabsTrigger value="delete">
                                    <IconoirIcon name="trash" className="text-base" />
                                    Hapus Akun
                                </TabsTrigger>
                            </>
                        )}
                    </TabsList>

                    <TabsContent value="profile" className="space-y-6" data-tour="profile-form">
                        <p className="text-sm text-text-secondary">
                            Perbarui informasi profil dan alamat email akun Anda.
                        </p>

                        <form onSubmit={submit} encType="multipart/form-data" className="space-y-6">
                            <fieldset disabled={!canUpdateProfile} className="space-y-6 disabled:opacity-75">
                                <div className="rounded-lg border border-border p-4">
                                    <Label>Foto profil</Label>
                                    <div className="mt-2 flex flex-wrap items-center gap-4">
                                        {avatarPreview ? (
                                            <img
                                                src={avatarPreview}
                                                alt="Preview"
                                                className="h-14 w-14 shrink-0 rounded-full border border-border object-cover"
                                            />
                                        ) : (
                                            <UserAvatar user={user} className="h-14 w-14" />
                                        )}
                                        <div className="min-w-[12rem] flex-1 space-y-3">
                                            <Input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => setData('avatar', e.target.files?.[0] ?? null)}
                                            />
                                            <p className="text-xs text-text-secondary">
                                                PNG/JPG, dikonversi ke WebP (maks. 256px).
                                            </p>
                                            {user.avatar_url && (
                                                <label className="inline-flex items-center gap-2 text-sm text-text-secondary">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.remove_avatar}
                                                        onChange={(e) => setData('remove_avatar', e.target.checked)}
                                                        className="rounded border-border text-primary focus:ring-primary"
                                                    />
                                                    Hapus foto profil
                                                </label>
                                            )}
                                            <InputError message={errors.avatar} />
                                        </div>
                                    </div>
                                </div>

                                <div className="grid gap-6 md:grid-cols-2">
                                    <div>
                                        <Label>Name</Label>
                                        <Input
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            autoFocus
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div>
                                        <Label>Email</Label>
                                        <Input
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.email} />
                                        {mustVerifyEmail && (
                                            <div className="mt-2">
                                                <p className="text-xs text-warning">
                                                    Your email address is unverified.{' '}
                                                    <Link
                                                        href={route('verification.send')}
                                                        method="post"
                                                        as="button"
                                                        className="font-semibold text-primary hover:underline"
                                                    >
                                                        Click here to re-send the verification email.
                                                    </Link>
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                    <div>
                                        <Label>Username</Label>
                                        <Input
                                            value={data.username}
                                            onChange={(e) => setData('username', e.target.value)}
                                        />
                                        <InputError message={errors.username} />
                                    </div>
                                </div>

                                {canUpdateProfile && (
                                    <div className="flex justify-end border-t border-border pt-4">
                                        <Button type="submit" disabled={processing}>
                                            Simpan
                                        </Button>
                                    </div>
                                )}
                            </fieldset>
                        </form>
                    </TabsContent>

                    {canUpdateProfile && (
                        <>
                            <TabsContent value="password">
                                <UpdatePasswordForm />
                            </TabsContent>
                            <TabsContent value="delete">
                                <DeleteUserForm />
                            </TabsContent>
                        </>
                    )}
                </Tabs>
            </div>
        </AppLayout>
    );
}
