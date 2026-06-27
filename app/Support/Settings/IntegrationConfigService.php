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
        $config = $this->cachedAppConfig();

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

    public function hajjParticipantEmailDomain(): string
    {
        $config = $this->cachedAppConfig();

        return $this->preferDb(
            $config?->hajj_participant_email_domain,
            'starterkit.hajj_participant_email_domain',
            'peserta-haji.local',
        );
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
            'mail_mailer' => $this->preferDb($config?->mail_mailer, 'mail.default', 'smtp'),
            'mail_host' => $this->preferDb($config?->mail_host, 'mail.mailers.smtp.host'),
            'mail_port' => $this->preferDbInt($config?->mail_port, 'mail.mailers.smtp.port', 0) ?: '',
            'mail_username' => $this->preferDb($config?->mail_username, 'mail.mailers.smtp.username'),
            'mail_encryption' => $this->preferDb($config?->mail_encryption, 'mail.mailers.smtp.encryption'),
            'mail_from_address' => $this->preferDb($config?->mail_from_address, 'mail.from.address'),
            'mail_from_name' => $this->preferDb($config?->mail_from_name, 'mail.from.name'),
            'has_mail_password' => filled($config?->mail_password),
            'has_postmark_api_key' => filled($config?->postmark_api_key),
            'has_resend_api_key' => filled($config?->resend_api_key),
            'ai_provider' => $this->preferDb($config?->ai_provider, 'ai.provider'),
            'ai_base_url' => $this->preferDb($config?->ai_base_url, 'ai.base_url'),
            'ai_model' => $this->preferDb($config?->ai_model, 'ai.model'),
            'has_ai_api_key' => filled($config?->ai_api_key),
            'registration_enabled' => $this->preferDbBool($config?->registration_enabled, 'starterkit.registration_enabled', true),
            'two_factor_enabled' => $this->preferDbBool($config?->two_factor_enabled, 'starterkit.two_factor_enabled', false),
            'security_csp_enabled' => $this->preferDbBool($config?->security_csp_enabled, 'security_headers.csp_enabled', false),
            'impersonation_timeout_minutes' => $this->preferDbInt($config?->impersonation_timeout_minutes, 'starterkit.impersonation_timeout_minutes', 0),
            'hajj_participant_role' => $this->preferDb($config?->hajj_participant_role, 'starterkit.hajj_participant_role', 'user'),
            'hajj_participant_email_domain' => $this->preferDb($config?->hajj_participant_email_domain, 'starterkit.hajj_participant_email_domain', 'peserta-haji.local'),
            'sentry_environment' => $this->preferDb($config?->sentry_environment, 'sentry.environment'),
            'sentry_traces_sample_rate' => $this->preferDbFloat($config?->sentry_traces_sample_rate, 'sentry.traces_sample_rate', 0),
            'has_sentry_dsn' => filled($config?->sentry_dsn),
            'slack_bot_channel' => $this->preferDb($config?->slack_bot_channel, 'services.slack.notifications.channel'),
            'has_slack_bot_token' => filled($config?->slack_bot_token),
        ];
    }

    private function cachedAppConfig(): ?AppConfig
    {
        if (! $this->tableExists()) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null && ! $cached instanceof AppConfig) {
            $this->forgetCache();
        }

        $config = Cache::rememberForever(self::CACHE_KEY, fn () => AppConfig::query()->first());

        return $config instanceof AppConfig ? $config : null;
    }

    private function applyGoogle(AppConfig $config): void
    {
        config([
            'services.google.client_id' => $this->preferDb($config->google_client_id, 'services.google.client_id'),
            'services.google.client_secret' => $this->preferDb($config->google_client_secret, 'services.google.client_secret'),
            'services.google.redirect' => $this->preferDb($config->google_redirect_uri, 'services.google.redirect'),
        ]);
    }

    private function applyMail(AppConfig $config): void
    {
        config([
            'mail.default' => $this->preferDb($config->mail_mailer, 'mail.default', 'smtp'),
            'mail.mailers.smtp.host' => $this->preferDb($config->mail_host, 'mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $this->preferDbInt($config->mail_port, 'mail.mailers.smtp.port', (int) config('mail.mailers.smtp.port', 587)),
            'mail.mailers.smtp.username' => $this->preferDb($config->mail_username, 'mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $this->preferDb($config->mail_password, 'mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => $this->preferDb($config->mail_encryption, 'mail.mailers.smtp.encryption'),
            'mail.from.address' => $this->preferDb($config->mail_from_address, 'mail.from.address'),
            'mail.from.name' => $this->preferDb($config->mail_from_name, 'mail.from.name'),
        ]);
    }

    private function applyAi(AppConfig $config): void
    {
        config([
            'ai.provider' => $this->preferDb($config->ai_provider, 'ai.provider'),
            'ai.api_key' => $this->preferDb($config->ai_api_key, 'ai.api_key'),
            'ai.base_url' => $this->preferDb($config->ai_base_url, 'ai.base_url'),
            'ai.model' => $this->preferDb($config->ai_model, 'ai.model'),
        ]);
    }

    private function applyApplication(AppConfig $config): void
    {
        config([
            'starterkit.registration_enabled' => $this->preferDbBool($config->registration_enabled, 'starterkit.registration_enabled', true),
            'starterkit.two_factor_enabled' => $this->preferDbBool($config->two_factor_enabled, 'starterkit.two_factor_enabled', false),
            'starterkit.impersonation_timeout_minutes' => $this->preferDbInt($config->impersonation_timeout_minutes, 'starterkit.impersonation_timeout_minutes', 0),
            'starterkit.hajj_participant_role' => $this->preferDb($config->hajj_participant_role, 'starterkit.hajj_participant_role', 'user'),
            'starterkit.hajj_participant_email_domain' => $this->preferDb($config->hajj_participant_email_domain, 'starterkit.hajj_participant_email_domain', 'peserta-haji.local'),
        ]);
    }

    private function applySecurity(AppConfig $config): void
    {
        config([
            'security_headers.csp_enabled' => $this->preferDbBool($config->security_csp_enabled, 'security_headers.csp_enabled', false),
        ]);
    }

    private function applySentry(AppConfig $config): void
    {
        config([
            'sentry.dsn' => $this->preferDb($config->sentry_dsn, 'sentry.dsn'),
            'sentry.environment' => $this->preferDb($config->sentry_environment, 'sentry.environment'),
            'sentry.traces_sample_rate' => $this->preferDbFloat($config->sentry_traces_sample_rate, 'sentry.traces_sample_rate', 0),
        ]);
    }

    private function applyExtraServices(AppConfig $config): void
    {
        config([
            'services.postmark.key' => $this->preferDb($config->postmark_api_key, 'services.postmark.key'),
            'services.resend.key' => $this->preferDb($config->resend_api_key, 'services.resend.key'),
            'services.slack.notifications.bot_user_oauth_token' => $this->preferDb($config->slack_bot_token, 'services.slack.notifications.bot_user_oauth_token'),
            'services.slack.notifications.channel' => $this->preferDb($config->slack_bot_channel, 'services.slack.notifications.channel'),
        ]);
    }

    /**
     * String config: DB → .env/config file → default.
     */
    private function preferDb(?string $dbValue, string $configKey, ?string $default = null): string
    {
        if (filled($dbValue)) {
            return $dbValue;
        }

        $fromEnv = config($configKey);

        if (filled($fromEnv)) {
            return (string) $fromEnv;
        }

        return $default ?? '';
    }

    /**
     * Boolean config: DB (termasuk false eksplisit) → .env/config file → default.
     */
    private function preferDbBool(?bool $dbValue, string $configKey, bool $default = false): bool
    {
        if ($dbValue !== null) {
            return $dbValue;
        }

        $fromEnv = config($configKey);

        return $fromEnv !== null ? (bool) $fromEnv : $default;
    }

    /**
     * Integer config: DB → .env/config file → default.
     */
    private function preferDbInt(?int $dbValue, string $configKey, int $default = 0): int
    {
        if ($dbValue !== null) {
            return $dbValue;
        }

        $fromEnv = config($configKey);

        return $fromEnv !== null ? (int) $fromEnv : $default;
    }

    /**
     * Float config: DB → .env/config file → default.
     */
    private function preferDbFloat(null|float|int $dbValue, string $configKey, float $default = 0.0): float
    {
        if ($dbValue !== null) {
            return (float) $dbValue;
        }

        $fromEnv = config($configKey);

        return $fromEnv !== null ? (float) $fromEnv : $default;
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
