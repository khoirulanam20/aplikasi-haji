import { FormEventHandler } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import IconoirIcon from '@/Components/IconoirIcon';
import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { PasswordInput } from '@/Components/ui/password-input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { AppConfigData, Role } from '@/types';

interface AiProviderOption {
    value: string;
    label: string;
}

interface ConfigIndexProps {
    config: AppConfigData;
    aiProviders: AiProviderOption[];
    roles: Role[];
    canSave: boolean;
}

const AI_MODEL_PLACEHOLDERS: Record<string, string> = {
    openai: 'gpt-4o-mini',
    anthropic: 'claude-3-5-sonnet-latest',
    custom: 'model-name',
};

export default function Index({ config, aiProviders, roles, canSave }: ConfigIndexProps) {
    const { data, setData, processing, errors } = useForm({
        google_client_id: config.google_client_id,
        google_client_secret: '',
        google_redirect_uri: config.google_redirect_uri,
        mail_mailer: config.mail_mailer,
        mail_host: config.mail_host,
        mail_port: config.mail_port === '' ? '' : String(config.mail_port),
        mail_username: config.mail_username,
        mail_password: '',
        mail_encryption: config.mail_encryption,
        mail_from_address: config.mail_from_address,
        mail_from_name: config.mail_from_name,
        postmark_api_key: '',
        resend_api_key: '',
        ai_provider: config.ai_provider,
        ai_api_key: '',
        ai_base_url: config.ai_base_url,
        ai_model: config.ai_model,
        registration_enabled: config.registration_enabled,
        two_factor_enabled: config.two_factor_enabled,
        security_csp_enabled: config.security_csp_enabled,
        impersonation_timeout_minutes: String(config.impersonation_timeout_minutes),
        hajj_participant_role: config.hajj_participant_role,
        hajj_participant_email_domain: config.hajj_participant_email_domain,
        sentry_dsn: '',
        sentry_environment: config.sentry_environment,
        sentry_traces_sample_rate: String(config.sentry_traces_sample_rate ?? 0),
        slack_bot_token: '',
        slack_bot_channel: config.slack_bot_channel,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        router.put(route('app.config.update'), data, {
            preserveScroll: true,
            onSuccess: () => {
                setData('google_client_secret', '');
                setData('mail_password', '');
                setData('ai_api_key', '');
                setData('postmark_api_key', '');
                setData('resend_api_key', '');
                setData('sentry_dsn', '');
                setData('slack_bot_token', '');
            },
        });
    };

    const secretPlaceholder = (hasValue: boolean) =>
        hasValue ? 'Kosongkan jika tidak diubah' : 'Belum diatur';

    return (
        <AppLayout header="Config">
            <Head title="Config" />

            {!canSave && (
                <div className="card mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Anda hanya memiliki akses lihat. Hubungi admin untuk mengubah konfigurasi.
                </div>
            )}

            <form onSubmit={submit}>
                <fieldset disabled={!canSave} className="disabled:opacity-75">
                    <div className="card">
                        <Tabs defaultValue="application" data-tour="config-tabs">
                            <TabsList className="flex h-auto flex-wrap gap-1">
                                <TabsTrigger value="application">
                                    <IconoirIcon name="settings" className="text-base" />
                                    Aplikasi
                                </TabsTrigger>
                                <TabsTrigger value="security">
                                    <IconoirIcon name="shield" className="text-base" />
                                    Keamanan
                                </TabsTrigger>
                                <TabsTrigger value="monitoring">
                                    <IconoirIcon name="activity" className="text-base" />
                                    Monitoring
                                </TabsTrigger>
                                <TabsTrigger value="notifications">
                                    <IconoirIcon name="bell" className="text-base" />
                                    Notifikasi
                                </TabsTrigger>
                                <TabsTrigger value="oauth">
                                    <IconoirIcon name="lock" className="text-base" />
                                    Google OAuth
                                </TabsTrigger>
                                <TabsTrigger value="smtp">
                                    <IconoirIcon name="mail" className="text-base" />
                                    Email SMTP
                                </TabsTrigger>
                                <TabsTrigger value="ai">
                                    <IconoirIcon name="key" className="text-base" />
                                    AI API
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="application" className="space-y-4">
                                <p className="text-sm text-text-secondary">
                                    Perilaku aplikasi dan provisioning akun peserta haji.
                                </p>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="registration_enabled"
                                        checked={data.registration_enabled}
                                        onCheckedChange={(checked) =>
                                            setData('registration_enabled', checked === true)
                                        }
                                    />
                                    <Label htmlFor="registration_enabled">Izinkan registrasi publik</Label>
                                </div>
                                <div>
                                    <Label>Role default peserta haji</Label>
                                    <Select
                                        value={data.hajj_participant_role || undefined}
                                        onValueChange={(value) => setData('hajj_participant_role', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roles.map((role) => (
                                                <SelectItem key={role.id} value={role.name}>
                                                    {role.title}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.hajj_participant_role} />
                                </div>
                                <div>
                                    <Label>Domain email peserta</Label>
                                    <Input
                                        value={data.hajj_participant_email_domain}
                                        onChange={(e) =>
                                            setData('hajj_participant_email_domain', e.target.value)
                                        }
                                        placeholder="peserta-haji.local"
                                    />
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Format akun otomatis: {'{username}'}@{'{domain}'}
                                    </p>
                                    <InputError message={errors.hajj_participant_email_domain} />
                                </div>
                            </TabsContent>

                            <TabsContent value="security" className="space-y-4">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="two_factor_enabled"
                                        checked={data.two_factor_enabled}
                                        onCheckedChange={(checked) =>
                                            setData('two_factor_enabled', checked === true)
                                        }
                                    />
                                    <Label htmlFor="two_factor_enabled">Aktifkan two-factor authentication (TOTP)</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="security_csp_enabled"
                                        checked={data.security_csp_enabled}
                                        onCheckedChange={(checked) =>
                                            setData('security_csp_enabled', checked === true)
                                        }
                                    />
                                    <Label htmlFor="security_csp_enabled">Aktifkan Content-Security-Policy</Label>
                                </div>
                                <div>
                                    <Label>Timeout impersonate (menit, 0 = tanpa batas)</Label>
                                    <Input
                                        type="number"
                                        min={0}
                                        value={data.impersonation_timeout_minutes}
                                        onChange={(e) =>
                                            setData('impersonation_timeout_minutes', e.target.value)
                                        }
                                    />
                                    <InputError message={errors.impersonation_timeout_minutes} />
                                </div>
                            </TabsContent>

                            <TabsContent value="monitoring" className="space-y-4">
                                <div>
                                    <Label>Sentry DSN</Label>
                                    <PasswordInput
                                        value={data.sentry_dsn}
                                        placeholder={secretPlaceholder(config.has_sentry_dsn)}
                                        onChange={(e) => setData('sentry_dsn', e.target.value)}
                                    />
                                    <InputError message={errors.sentry_dsn} />
                                </div>
                                <div>
                                    <Label>Sentry Environment</Label>
                                    <Input
                                        value={data.sentry_environment}
                                        onChange={(e) => setData('sentry_environment', e.target.value)}
                                        placeholder="production"
                                    />
                                    <InputError message={errors.sentry_environment} />
                                </div>
                                <div>
                                    <Label>Traces Sample Rate (0–1)</Label>
                                    <Input
                                        type="number"
                                        min={0}
                                        max={1}
                                        step={0.01}
                                        value={data.sentry_traces_sample_rate}
                                        onChange={(e) =>
                                            setData('sentry_traces_sample_rate', e.target.value)
                                        }
                                    />
                                    <InputError message={errors.sentry_traces_sample_rate} />
                                </div>
                            </TabsContent>

                            <TabsContent value="notifications" className="space-y-4">
                                <div>
                                    <Label>Slack Bot Token</Label>
                                    <PasswordInput
                                        value={data.slack_bot_token}
                                        placeholder={secretPlaceholder(config.has_slack_bot_token)}
                                        onChange={(e) => setData('slack_bot_token', e.target.value)}
                                    />
                                    <InputError message={errors.slack_bot_token} />
                                </div>
                                <div>
                                    <Label>Slack Default Channel</Label>
                                    <Input
                                        value={data.slack_bot_channel}
                                        onChange={(e) => setData('slack_bot_channel', e.target.value)}
                                        placeholder="#notifications"
                                    />
                                    <InputError message={errors.slack_bot_channel} />
                                </div>
                            </TabsContent>

                            <TabsContent value="oauth" className="space-y-4" data-tour="config-form">
                                <p className="text-sm text-text-secondary">
                                    Kredensial login Google untuk tombol Sign in with Google.
                                </p>
                                <div>
                                    <Label>Client ID</Label>
                                    <Input
                                        value={data.google_client_id}
                                        onChange={(e) => setData('google_client_id', e.target.value)}
                                    />
                                    <InputError message={errors.google_client_id} />
                                </div>
                                <div>
                                    <Label>Client Secret</Label>
                                    <PasswordInput
                                        value={data.google_client_secret}
                                        placeholder={secretPlaceholder(config.has_google_client_secret)}
                                        onChange={(e) => setData('google_client_secret', e.target.value)}
                                    />
                                    <InputError message={errors.google_client_secret} />
                                </div>
                                <div>
                                    <Label>Redirect URI</Label>
                                    <Input
                                        value={data.google_redirect_uri}
                                        onChange={(e) => setData('google_redirect_uri', e.target.value)}
                                        placeholder={`${window.location.origin}/auth/google/callback`}
                                    />
                                    <InputError message={errors.google_redirect_uri} />
                                </div>
                            </TabsContent>

                            <TabsContent value="smtp" className="space-y-4">
                                <p className="text-sm text-text-secondary">
                                    Pengaturan pengiriman email via SMTP dan provider tambahan.
                                </p>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label>Mailer</Label>
                                        <Input
                                            value={data.mail_mailer}
                                            onChange={(e) => setData('mail_mailer', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>Host</Label>
                                        <Input
                                            value={data.mail_host}
                                            onChange={(e) => setData('mail_host', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>Port</Label>
                                        <Input
                                            type="number"
                                            value={data.mail_port}
                                            onChange={(e) => setData('mail_port', e.target.value)}
                                        />
                                        <InputError message={errors.mail_port} />
                                    </div>
                                    <div>
                                        <Label>Encryption</Label>
                                        <Select
                                            value={data.mail_encryption || 'none'}
                                            onValueChange={(value) =>
                                                setData('mail_encryption', value === 'none' ? '' : value)
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih encryption" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">None</SelectItem>
                                                <SelectItem value="tls">TLS</SelectItem>
                                                <SelectItem value="ssl">SSL</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label>Username</Label>
                                        <Input
                                            value={data.mail_username}
                                            onChange={(e) => setData('mail_username', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>Password</Label>
                                        <PasswordInput
                                            value={data.mail_password}
                                            placeholder={secretPlaceholder(config.has_mail_password)}
                                            onChange={(e) => setData('mail_password', e.target.value)}
                                        />
                                        <InputError message={errors.mail_password} />
                                    </div>
                                    <div>
                                        <Label>From Address</Label>
                                        <Input
                                            type="email"
                                            value={data.mail_from_address}
                                            onChange={(e) => setData('mail_from_address', e.target.value)}
                                        />
                                        <InputError message={errors.mail_from_address} />
                                    </div>
                                    <div>
                                        <Label>From Name</Label>
                                        <Input
                                            value={data.mail_from_name}
                                            onChange={(e) => setData('mail_from_name', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>Postmark API Key</Label>
                                        <PasswordInput
                                            value={data.postmark_api_key}
                                            placeholder={secretPlaceholder(config.has_postmark_api_key)}
                                            onChange={(e) => setData('postmark_api_key', e.target.value)}
                                        />
                                        <InputError message={errors.postmark_api_key} />
                                    </div>
                                    <div>
                                        <Label>Resend API Key</Label>
                                        <PasswordInput
                                            value={data.resend_api_key}
                                            placeholder={secretPlaceholder(config.has_resend_api_key)}
                                            onChange={(e) => setData('resend_api_key', e.target.value)}
                                        />
                                        <InputError message={errors.resend_api_key} />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent value="ai" className="space-y-4">
                                <p className="text-sm text-text-secondary">
                                    Provider dan kredensial AI untuk fitur yang membutuhkan LLM.
                                </p>
                                <div>
                                    <Label>Provider</Label>
                                    <Select
                                        value={data.ai_provider || 'none'}
                                        onValueChange={(value) =>
                                            setData('ai_provider', value === 'none' ? '' : value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih provider" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Tidak aktif</SelectItem>
                                            {aiProviders.map((provider) => (
                                                <SelectItem key={provider.value} value={provider.value}>
                                                    {provider.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.ai_provider} />
                                </div>
                                <div>
                                    <Label>API Key</Label>
                                    <PasswordInput
                                        value={data.ai_api_key}
                                        placeholder={secretPlaceholder(config.has_ai_api_key)}
                                        onChange={(e) => setData('ai_api_key', e.target.value)}
                                    />
                                    <InputError message={errors.ai_api_key} />
                                </div>
                                {data.ai_provider === 'custom' && (
                                    <div>
                                        <Label>Base URL</Label>
                                        <Input
                                            value={data.ai_base_url}
                                            onChange={(e) => setData('ai_base_url', e.target.value)}
                                            placeholder="https://api.example.com/v1"
                                        />
                                        <InputError message={errors.ai_base_url} />
                                    </div>
                                )}
                                <div>
                                    <Label>Model</Label>
                                    <Input
                                        value={data.ai_model}
                                        onChange={(e) => setData('ai_model', e.target.value)}
                                        placeholder={
                                            data.ai_provider
                                                ? AI_MODEL_PLACEHOLDERS[data.ai_provider] ?? 'model-name'
                                                : 'Pilih provider terlebih dahulu'
                                        }
                                    />
                                    <InputError message={errors.ai_model} />
                                </div>
                            </TabsContent>
                        </Tabs>

                        {canSave && (
                            <div className="mt-6 flex justify-end border-t border-border pt-4">
                                <Button type="submit" disabled={processing}>
                                    Simpan
                                </Button>
                            </div>
                        )}
                    </div>
                </fieldset>
            </form>
        </AppLayout>
    );
}
