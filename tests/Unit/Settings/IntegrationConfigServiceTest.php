<?php

namespace Tests\Unit\Settings;

use App\Models\AppConfig;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IntegrationConfigServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'env-client-id',
            'services.google.client_secret' => 'env-client-secret',
            'mail.mailers.smtp.host' => 'env-host',
            'ai.provider' => 'openai',
            'ai.api_key' => 'env-ai-key',
        ]);
    }

    public function test_db_values_override_env_when_present(): void
    {
        AppConfig::singleton()->update([
            'google_client_id' => 'db-client-id',
            'google_client_secret' => 'db-client-secret',
            'mail_host' => 'db-host',
            'ai_provider' => 'anthropic',
            'ai_api_key' => 'db-ai-key',
        ]);

        $service = app(IntegrationConfigService::class);
        $service->forgetCache();
        $service->apply();

        $this->assertSame('db-client-id', config('services.google.client_id'));
        $this->assertSame('db-client-secret', config('services.google.client_secret'));
        $this->assertSame('db-host', config('mail.mailers.smtp.host'));
        $this->assertSame('anthropic', config('ai.provider'));
        $this->assertSame('db-ai-key', config('ai.api_key'));
    }

    public function test_env_fallback_when_db_field_is_empty(): void
    {
        AppConfig::singleton()->update([
            'google_client_id' => null,
            'google_client_secret' => null,
            'mail_host' => null,
            'ai_api_key' => null,
        ]);

        $service = app(IntegrationConfigService::class);
        $service->forgetCache();
        Cache::forget('app_config_applied');
        $service->apply();

        $this->assertSame('env-client-id', config('services.google.client_id'));
        $this->assertSame('env-client-secret', config('services.google.client_secret'));
        $this->assertSame('env-host', config('mail.mailers.smtp.host'));
        $this->assertSame('env-ai-key', config('ai.api_key'));
    }

    public function test_application_settings_override_env_when_present(): void
    {
        config([
            'starterkit.registration_enabled' => true,
            'starterkit.two_factor_enabled' => false,
            'starterkit.hajj_participant_email_domain' => 'peserta-haji.local',
            'security_headers.csp_enabled' => false,
        ]);

        AppConfig::singleton()->update([
            'registration_enabled' => false,
            'two_factor_enabled' => true,
            'hajj_participant_email_domain' => 'haji.example.com',
            'security_csp_enabled' => true,
            'impersonation_timeout_minutes' => 30,
            'hajj_participant_role' => 'user',
        ]);

        $service = app(IntegrationConfigService::class);
        $service->forgetCache();
        Cache::forget('app_config_applied');
        $service->apply();

        $this->assertFalse(config('starterkit.registration_enabled'));
        $this->assertTrue(config('starterkit.two_factor_enabled'));
        $this->assertSame('haji.example.com', config('starterkit.hajj_participant_email_domain'));
        $this->assertTrue(config('security_headers.csp_enabled'));
        $this->assertSame(30, config('starterkit.impersonation_timeout_minutes'));
    }

    public function test_sentry_and_extra_services_override_env(): void
    {
        config([
            'sentry.dsn' => 'env-dsn',
            'services.postmark.key' => 'env-postmark',
            'services.slack.notifications.channel' => '#env',
        ]);

        AppConfig::singleton()->update([
            'sentry_dsn' => 'db-dsn',
            'sentry_environment' => 'staging',
            'sentry_traces_sample_rate' => 0.5,
            'postmark_api_key' => 'db-postmark',
            'slack_bot_channel' => '#alerts',
        ]);

        $service = app(IntegrationConfigService::class);
        $service->forgetCache();
        Cache::forget('app_config_applied');
        $service->apply();

        $this->assertSame('db-dsn', config('sentry.dsn'));
        $this->assertSame('staging', config('sentry.environment'));
        $this->assertSame(0.5, config('sentry.traces_sample_rate'));
        $this->assertSame('db-postmark', config('services.postmark.key'));
        $this->assertSame('#alerts', config('services.slack.notifications.channel'));
    }

    public function test_apply_still_overrides_env_when_cache_is_warm(): void
    {
        config(['starterkit.hajj_participant_email_domain' => 'peserta-haji.local']);

        AppConfig::singleton()->update([
            'hajj_participant_email_domain' => 'haji.example.com',
        ]);

        $service = app(IntegrationConfigService::class);
        $service->forgetCache();
        $service->apply();
        $service->apply();

        $this->assertSame('haji.example.com', config('starterkit.hajj_participant_email_domain'));
    }

    public function test_google_oauth_enabled_requires_client_id_and_secret(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $this->assertFalse(app(IntegrationConfigService::class)->googleOAuthEnabled());

        config([
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $this->assertTrue(app(IntegrationConfigService::class)->googleOAuthEnabled());
    }
}
