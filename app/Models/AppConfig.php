<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class AppConfig extends Model
{
    use LogsModelActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return $this->baseActivitylogOptions()
            ->logExcept([
                'google_client_secret',
                'mail_password',
                'ai_api_key',
                'sentry_dsn',
                'postmark_api_key',
                'resend_api_key',
                'slack_bot_token',
            ]);
    }

    public const AI_PROVIDERS = ['openai', 'anthropic', 'custom'];

    protected $fillable = [
        'google_client_id',
        'google_client_secret',
        'google_redirect_uri',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'ai_provider',
        'ai_api_key',
        'ai_base_url',
        'ai_model',
        'registration_enabled',
        'two_factor_enabled',
        'security_csp_enabled',
        'impersonation_timeout_minutes',
        'hajj_participant_role',
        'hajj_participant_email_domain',
        'sentry_dsn',
        'sentry_environment',
        'sentry_traces_sample_rate',
        'postmark_api_key',
        'resend_api_key',
        'slack_bot_token',
        'slack_bot_channel',
    ];

    protected function casts(): array
    {
        return [
            'google_client_secret' => 'encrypted',
            'mail_password' => 'encrypted',
            'ai_api_key' => 'encrypted',
            'sentry_dsn' => 'encrypted',
            'postmark_api_key' => 'encrypted',
            'resend_api_key' => 'encrypted',
            'slack_bot_token' => 'encrypted',
            'mail_port' => 'integer',
            'registration_enabled' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'security_csp_enabled' => 'boolean',
            'impersonation_timeout_minutes' => 'integer',
            'sentry_traces_sample_rate' => 'float',
        ];
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
