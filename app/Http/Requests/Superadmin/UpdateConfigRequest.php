<?php

namespace App\Http\Requests\Superadmin;

use App\Models\AppConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_redirect_uri' => ['nullable', 'string', 'max:500'],
            'mail_mailer' => ['nullable', 'string', 'max:50'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'postmark_api_key' => ['nullable', 'string', 'max:500'],
            'resend_api_key' => ['nullable', 'string', 'max:500'],
            'ai_provider' => ['nullable', Rule::in(AppConfig::AI_PROVIDERS)],
            'ai_api_key' => ['nullable', 'string', 'max:500'],
            'ai_base_url' => ['nullable', 'url', 'max:500'],
            'ai_model' => ['nullable', 'string', 'max:255'],
            'registration_enabled' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
            'security_csp_enabled' => ['nullable', 'boolean'],
            'impersonation_timeout_minutes' => ['nullable', 'integer', 'min:0'],
            'hajj_participant_role' => ['nullable', 'string', 'max:100', 'exists:roles,name'],
            'hajj_participant_email_domain' => ['nullable', 'string', 'max:255', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i'],
            'sentry_dsn' => ['nullable', 'string', 'max:500'],
            'sentry_environment' => ['nullable', 'string', 'max:100'],
            'sentry_traces_sample_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'slack_bot_token' => ['nullable', 'string', 'max:500'],
            'slack_bot_channel' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('ai_provider') === 'custom' && empty($this->input('ai_base_url'))) {
                $validator->errors()->add('ai_base_url', 'Base URL wajib diisi untuk provider Custom.');
            }
        });
    }
}
