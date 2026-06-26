import { FormEventHandler, useEffect, useMemo } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm';
import InputError from '@/Components/InputError';
import UserAvatar from '@/Components/UserAvatar';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { HajjParticipantProfile, PageProps, User } from '@/types';

interface PesertaEditProps {
    user: User;
    participant: HajjParticipantProfile;
    canUpdateProfile: boolean;
    mustVerifyEmail: boolean;
    status?: string;
}

function ReadOnlyField({ label, value }: { label: string; value: string | number | null }) {
    return (
        <div>
            <Label>{label}</Label>
            <p className="mt-1.5 rounded-md border border-border bg-muted/30 px-3 py-2 text-sm text-text-primary">
                {value || '—'}
            </p>
        </div>
    );
}

export default function PesertaEdit({
    user,
    participant,
    canUpdateProfile,
    mustVerifyEmail,
    status: _status,
}: PesertaEditProps) {
    const { flash } = usePage<PageProps>().props;

    const { data, setData, patch, processing, errors } = useForm({
        name: user.name,
        username: user.username ?? '',
        email: user.email,
        avatar: null as File | null,
        remove_avatar: false,
        telepon: participant.telepon ?? '',
        alamat: participant.alamat ?? '',
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

        const payload = {
            name: data.name,
            username: data.username,
            email: data.email,
            telepon: data.telepon,
            alamat: data.alamat,
            ...(data.remove_avatar ? { remove_avatar: true } : {}),
        };

        if (hasAvatarFile) {
            router.post(
                route('profile.update'),
                {
                    _method: 'patch',
                    ...payload,
                    avatar: data.avatar,
                },
                { ...options, forceFormData: true },
            );

            return;
        }

        patch(route('profile.update'), options);
    };

    return (
        <AppLayout header="Profil Peserta">
            <Head title="Profil Peserta" />

            {!canUpdateProfile && (
                <div className="card mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Anda hanya memiliki akses lihat profil. Hubungi admin untuk mengubah data.
                </div>
            )}

            {flash?.type === 'success' && (
                <div className="card mb-6 rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-sm text-success">
                    {flash.message}
                </div>
            )}

            <div className="card">
                <Tabs defaultValue="haji" data-tour="profile-tabs">
                    <TabsList>
                        <TabsTrigger value="haji">
                            <IconoirIcon name="user-circle" className="text-base" />
                            Data Haji
                        </TabsTrigger>
                        <TabsTrigger value="akun">
                            <IconoirIcon name="user" className="text-base" />
                            Akun
                        </TabsTrigger>
                        {canUpdateProfile && (
                            <TabsTrigger value="password">
                                <IconoirIcon name="lock" className="text-base" />
                                Password
                            </TabsTrigger>
                        )}
                    </TabsList>

                    <TabsContent value="haji" className="space-y-6">
                        <p className="text-sm text-text-secondary">
                            Data keberangkatan haji Anda. Anda dapat memperbarui telepon dan alamat.
                        </p>

                        <form onSubmit={submit} className="space-y-6">
                            <fieldset disabled={!canUpdateProfile} className="space-y-6 disabled:opacity-75">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <ReadOnlyField label="Tahun Haji" value={participant.tahun_haji} />
                                    <ReadOnlyField label="Nomor Porsi" value={participant.nomor_porsi} />
                                    <ReadOnlyField label="Nama" value={participant.nama} />
                                    <ReadOnlyField label="Kloter" value={participant.kloter} />
                                    <ReadOnlyField label="Rombongan" value={participant.rombongan} />
                                    <ReadOnlyField label="Regu" value={participant.regu} />
                                    <ReadOnlyField label="Desa" value={participant.desa} />
                                    <ReadOnlyField label="Kecamatan" value={participant.kecamatan} />
                                    <div>
                                        <Label htmlFor="telepon">Telepon</Label>
                                        <Input
                                            id="telepon"
                                            value={data.telepon}
                                            onChange={(e) => setData('telepon', e.target.value)}
                                            placeholder="08xxxxxxxxxx"
                                            className="mt-1.5"
                                        />
                                        <InputError message={errors.telepon} className="mt-1" />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Label htmlFor="alamat">Alamat</Label>
                                        <textarea
                                            id="alamat"
                                            value={data.alamat}
                                            onChange={(e) => setData('alamat', e.target.value)}
                                            rows={3}
                                            className="mt-1.5 flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                        <InputError message={errors.alamat} className="mt-1" />
                                    </div>
                                </div>

                                {canUpdateProfile && (
                                    <div className="flex justify-end border-t border-border pt-4">
                                        <Button type="submit" disabled={processing}>
                                            Simpan Data Haji
                                        </Button>
                                    </div>
                                )}
                            </fieldset>
                        </form>
                    </TabsContent>

                    <TabsContent value="akun" className="space-y-6" data-tour="profile-form">
                        <p className="text-sm text-text-secondary">
                            Perbarui informasi akun login Anda.
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
                                        <Label>Nama</Label>
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
                                                    Email belum diverifikasi.{' '}
                                                    <Link
                                                        href={route('verification.send')}
                                                        method="post"
                                                        as="button"
                                                        className="font-semibold text-primary hover:underline"
                                                    >
                                                        Kirim ulang email verifikasi
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
                                            Simpan Akun
                                        </Button>
                                    </div>
                                )}
                            </fieldset>
                        </form>
                    </TabsContent>

                    {canUpdateProfile && (
                        <TabsContent value="password">
                            <UpdatePasswordForm />
                        </TabsContent>
                    )}
                </Tabs>
            </div>
        </AppLayout>
    );
}
