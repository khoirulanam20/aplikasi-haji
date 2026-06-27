<?php

namespace App\Support\Settings;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class IntegrationConfigService
{
    private const CACHE_KEY = 'app_config_row';

    private const LEGACY_CACHE_KEY = 'app_config_applied';

    public function apply(): void
    {
        if (! $this->tableExists()) {
            return;
        }

        $config = Cache::rememberForever(self::CACHE_KEY, fn () => AppConfig::query()->first());

        if (! $config instanceof AppConfig) {
            return;
        }

        $this->applyGoogle($config);
        $this->applyMail($config);
        $this->applyAi($config);
        $this->applyApplication($config);
        $this->applySecurity($config);
        $this->applySentry($config);
        $this->applyExtraServices($config);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::LEGACY_CACHE_KEY);
    }

    public function googleOAuthEnabled(): bool
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        return filled($clientId) && filled($clientSecret);
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendArray(?AppConfig $config): array
    {
        return [
            'google_client_id' => $config?->google_client_id ?? '',
            'google_redirect_uri' => $config?->google_redirect_uri ?? '',
            'has_google_client_secret' => filled($config?->google_client_secret),
            'mail_mailer' => $config?->mail_mailer ?? 'smtp',
            'mail_host' => $config?->mail_host ?? '',
            'mail_port' => $config?->mail_port ?? '',
            'mail_username' => $config?->mail_username ?? '',
            'mail_encryption' => $config?->mail_encryption ?? '',
            'mail_from_address' => $config?->mail_from_address ?? '',
            'mail_from_name' => $config?->mail_from_name ?? '',
            'has_mail_password' => filled($config?->mail_password),
            'has_postmark_api_key' => filled($config?->postmark_api_key),
            'has_resend_api_key' => filled($config?->resend_api_key),
            'ai_provider' => $config?->ai_provider ?? '',
            'ai_base_url' => $config?->ai_base_url ?? '',
            'ai_model' => $config?->ai_model ?? '',
            'has_ai_api_key' => filled($config?->ai_api_key),
            'registration_enabled' => $config?->registration_enabled ?? config('starterkit.registration_enabled', true),
            'two_factor_enabled' => $config?->two_factor_enabled ?? config('starterkit.two_factor_enabled', false),
            'security_csp_enabled' => $config?->security_csp_enabled ?? config('security_headers.csp_enabled', false),
            'impersonation_timeout_minutes' => $config?->impersonation_timeout_minutes ?? config('starterkit.impersonation_timeout_minutes', 0),
            'hajj_participant_role' => $config?->hajj_participant_role ?? config('starterkit.hajj_participant_role', 'user'),
            'hajj_participant_email_domain' => $config?->hajj_participant_email_domain ?? config('starterkit.hajj_participant_email_domain', 'peserta-haji.local'),
            'sentry_environment' => $config?->sentry_environment ?? config('sentry.environment', ''),
            'sentry_traces_sample_rate' => $config?->sentry_traces_sample_rate ?? config('sentry.traces_sample_rate', 0),
            'has_sentry_dsn' => filled($config?->sentry_dsn),
            'slack_bot_channel' => $config?->slack_bot_channel ?? '',
            'has_slack_bot_token' => filled($config?->slack_bot_token),
        ];
    }

    private function applyGoogle(AppConfig $config): void
    {
        config([
            'services.google.client_id' => $config->google_client_id ?: config('services.google.client_id'),
            'services.google.client_secret' => $config->google_client_secret ?: config('services.google.client_secret'),
            'services.google.redirect' => $config->google_redirect_uri ?: config('services.google.redirect'),
        ]);
    }

    private function applyMail(AppConfig $config): void
    {
        $mailer = $config->mail_mailer ?: config('mail.default', 'smtp');

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $config->mail_host ?: config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $config->mail_port ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $config->mail_username ?: config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $config->mail_password ?: config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => $config->mail_encryption ?: config('mail.mailers.smtp.encryption'),
            'mail.from.address' => $config->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $config->mail_from_name ?: config('mail.from.name'),
        ]);
    }

    private function applyAi(AppConfig $config): void
    {
        config([
            'ai.provider' => $config->ai_provider ?: config('ai.provider'),
            'ai.api_key' => $config->ai_api_key ?: config('ai.api_key'),
            'ai.base_url' => $config->ai_base_url ?: config('ai.base_url'),
            'ai.model' => $config->ai_model ?: config('ai.model'),
        ]);
    }

    private function applyApplication(AppConfig $config): void
    {
        config([
            'starterkit.registration_enabled' => $config->registration_enabled ?? config('starterkit.registration_enabled'),
            'starterkit.two_factor_enabled' => $config->two_factor_enabled ?? config('starterkit.two_factor_enabled'),
            'starterkit.impersonation_timeout_minutes' => $config->impersonation_timeout_minutes ?? config('starterkit.impersonation_timeout_minutes'),
            'starterkit.hajj_participant_role' => $config->hajj_participant_role ?: config('starterkit.hajj_participant_role'),
            'starterkit.hajj_participant_email_domain' => $config->hajj_participant_email_domain ?: config('starterkit.hajj_participant_email_domain'),
        ]);
    }

    private function applySecurity(AppConfig $config): void
    {
        config([
            'security_headers.csp_enabled' => $config->security_csp_enabled ?? config('security_headers.csp_enabled'),
        ]);
    }

    private function applySentry(AppConfig $config): void
    {
        config([
            'sentry.dsn' => $config->sentry_dsn ?: config('sentry.dsn'),
            'sentry.environment' => $config->sentry_environment ?: config('sentry.environment'),
            'sentry.traces_sample_rate' => $config->sentry_traces_sample_rate ?? config('sentry.traces_sample_rate'),
        ]);
    }

    private function applyExtraServices(AppConfig $config): void
    {
        config([
            'services.postmark.key' => $config->postmark_api_key ?: config('services.postmark.key'),
            'services.resend.key' => $config->resend_api_key ?: config('services.resend.key'),
            'services.slack.notifications.bot_user_oauth_token' => $config->slack_bot_token ?: config('services.slack.notifications.bot_user_oauth_token'),
            'services.slack.notifications.channel' => $config->slack_bot_channel ?: config('services.slack.notifications.channel'),
        ]);
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('app_configs');
        } catch (\Throwable) {
            return false;
        }
    }
}
