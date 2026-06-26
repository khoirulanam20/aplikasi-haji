import { FormEventHandler } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import ImageDropzone from '@/Components/ui/image-dropzone';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { WebSettingData } from '@/types';

interface WebSettingIndexProps {
    setting: WebSettingData | null;
    canSave: boolean;
}

export default function Index({ setting, canSave }: WebSettingIndexProps) {
    const { data, setData, processing, errors } = useForm({
        app_name: setting?.app_name ?? '',
        app_tagline: setting?.app_tagline ?? '',
        site_description: setting?.site_description ?? '',
        primary_color: setting?.primary_color ?? '#FF5B37',
        secondary_color: setting?.secondary_color ?? '#4B5694',
        logo: null as File | null,
        favicon: null as File | null,
        remove_logo: false,
        remove_favicon: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        const hasNewFiles = data.logo instanceof File || data.favicon instanceof File;
        const hasRemovals = data.remove_logo || data.remove_favicon;

        const payload: Record<string, string | File | boolean> = {
            app_name: data.app_name,
            app_tagline: data.app_tagline,
            site_description: data.site_description,
            primary_color: data.primary_color,
            secondary_color: data.secondary_color,
            remove_logo: data.remove_logo,
            remove_favicon: data.remove_favicon,
        };

        if (data.logo instanceof File) {
            payload.logo = data.logo;
        }

        if (data.favicon instanceof File) {
            payload.favicon = data.favicon;
        }

        const onSuccess = () => {
            setData('logo', null);
            setData('favicon', null);
            setData('remove_logo', false);
            setData('remove_favicon', false);
        };

        if (hasNewFiles || hasRemovals) {
            router.post(
                route('app.web-setting.update'),
                { _method: 'put', ...payload },
                {
                    forceFormData: true,
                    preserveScroll: true,
                    onSuccess,
                },
            );

            return;
        }

        router.put(route('app.web-setting.update'), payload, {
            preserveScroll: true,
            onSuccess,
        });
    };

    return (
        <AppLayout header="Pengaturan Website">
            <Head title="Pengaturan Website" />

            {!canSave && (
                <div className="card mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Anda hanya memiliki akses lihat. Hubungi admin untuk mengubah pengaturan website.
                </div>
            )}

            <form onSubmit={submit} className="card space-y-6">
                <header className="flex items-center gap-3 border-b border-border pb-4" data-tour="websetting-identity">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-orange-50 text-primary">
                        <IconoirIcon name="settings" className="text-xl" />
                    </div>
                    <div>
                        <h2 className="text-lg font-bold text-text-primary">Identitas Aplikasi</h2>
                        <p className="text-sm text-text-secondary">Nama, tagline, dan deskripsi yang tampil di UI.</p>
                    </div>
                </header>

                <fieldset disabled={!canSave} className="space-y-6 disabled:opacity-75">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <Label>Nama Aplikasi</Label>
                            <Input
                                value={data.app_name}
                                onChange={(e) => setData('app_name', e.target.value)}
                                required
                            />
                            <InputError message={errors.app_name} />
                        </div>
                        <div>
                            <Label>Tagline</Label>
                            <Input
                                value={data.app_tagline}
                                onChange={(e) => setData('app_tagline', e.target.value)}
                            />
                        </div>
                    </div>

                    <div>
                        <Label>Deskripsi Situs</Label>
                        <textarea
                            className="input min-h-[80px] w-full"
                            rows={3}
                            value={data.site_description}
                            onChange={(e) => setData('site_description', e.target.value)}
                        />
                    </div>

                    <div className="space-y-4 rounded-lg border border-border p-4" data-tour="websetting-theme">
                        <p className="text-sm font-semibold">Warna Tema</p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label>Primary</Label>
                                <div className="mt-1 flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={data.primary_color}
                                        onChange={(e) => setData('primary_color', e.target.value)}
                                        className="h-10 w-14 cursor-pointer rounded border border-border"
                                    />
                                    <Input
                                        value={data.primary_color}
                                        onChange={(e) => setData('primary_color', e.target.value)}
                                        className="font-mono"
                                    />
                                </div>
                                <InputError message={errors.primary_color} />
                            </div>
                            <div>
                                <Label>Secondary</Label>
                                <div className="mt-1 flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={data.secondary_color}
                                        onChange={(e) => setData('secondary_color', e.target.value)}
                                        className="h-10 w-14 cursor-pointer rounded border border-border"
                                    />
                                    <Input
                                        value={data.secondary_color}
                                        onChange={(e) => setData('secondary_color', e.target.value)}
                                        className="font-mono"
                                    />
                                </div>
                                <InputError message={errors.secondary_color} />
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-2" data-tour="websetting-branding">
                        <ImageDropzone
                            label="Logo"
                            currentUrl={data.remove_logo ? null : setting?.logo_url}
                            value={data.logo}
                            onChange={(file) => {
                                setData('logo', file);
                                if (file) {
                                    setData('remove_logo', false);
                                }
                            }}
                            onRemove={() => setData('remove_logo', true)}
                            supportText="PNG, JPG, WebP maks. 5MB"
                            error={errors.logo}
                        />
                        <ImageDropzone
                            label="Favicon"
                            currentUrl={data.remove_favicon ? null : setting?.favicon_url}
                            value={data.favicon}
                            onChange={(file) => {
                                setData('favicon', file);
                                if (file) {
                                    setData('remove_favicon', false);
                                }
                            }}
                            onRemove={() => setData('remove_favicon', true)}
                            supportText="PNG, JPG, WebP maks. 2MB"
                            previewBoxClassName="h-16 w-16"
                            error={errors.favicon}
                        />
                    </div>

                    {canSave && (
                        <div className="flex justify-end border-t border-border pt-4">
                            <Button type="submit" disabled={processing}>
                                Simpan
                            </Button>
                        </div>
                    )}
                </fieldset>
            </form>
        </AppLayout>
    );
}
