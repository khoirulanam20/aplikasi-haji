<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\UpdateConfigRequest;
use App\Models\AppConfig;
use App\Models\Role;
use App\Support\Modules\FormModuleAccess;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConfigController extends Controller
{
    public function __construct(
        private readonly FormModuleAccess $formAccess,
        private readonly IntegrationConfigService $integrationConfig,
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $config = AppConfig::query()->first();

        return Inertia::render('Admin/Config/Index', [
            'config' => $this->integrationConfig->toFrontendArray($config),
            'aiProviders' => [
                ['value' => 'openai', 'label' => 'OpenAI'],
                ['value' => 'anthropic', 'label' => 'Anthropic'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'roles' => Role::query()->where('is_active', true)->orderBy('title')->get(['id', 'name', 'title']),
            'canSave' => $this->formAccess->canSave('config', $user),
        ]);
    }

    public function update(UpdateConfigRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $config = AppConfig::singleton();

        $data = [
            'google_client_id' => ($payload['google_client_id'] ?? null) ?: null,
            'google_redirect_uri' => ($payload['google_redirect_uri'] ?? null) ?: null,
            'mail_mailer' => ($payload['mail_mailer'] ?? null) ?: 'smtp',
            'mail_host' => ($payload['mail_host'] ?? null) ?: null,
            'mail_port' => $payload['mail_port'] ?? null,
            'mail_username' => ($payload['mail_username'] ?? null) ?: null,
            'mail_encryption' => ($payload['mail_encryption'] ?? null) ?: null,
            'mail_from_address' => ($payload['mail_from_address'] ?? null) ?: null,
            'mail_from_name' => ($payload['mail_from_name'] ?? null) ?: null,
            'ai_provider' => ($payload['ai_provider'] ?? null) ?: null,
            'ai_base_url' => ($payload['ai_base_url'] ?? null) ?: null,
            'ai_model' => ($payload['ai_model'] ?? null) ?: null,
            'registration_enabled' => $request->boolean('registration_enabled'),
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            'security_csp_enabled' => $request->boolean('security_csp_enabled'),
            'impersonation_timeout_minutes' => (int) ($payload['impersonation_timeout_minutes'] ?? 0),
            'hajj_participant_role' => ($payload['hajj_participant_role'] ?? null) ?: null,
            'hajj_participant_email_domain' => ($payload['hajj_participant_email_domain'] ?? null) ?: null,
            'sentry_environment' => ($payload['sentry_environment'] ?? null) ?: null,
            'sentry_traces_sample_rate' => isset($payload['sentry_traces_sample_rate'])
                ? (float) $payload['sentry_traces_sample_rate']
                : null,
            'slack_bot_channel' => ($payload['slack_bot_channel'] ?? null) ?: null,
        ];

        if (filled($payload['google_client_secret'] ?? null)) {
            $data['google_client_secret'] = $payload['google_client_secret'];
        }

        if (filled($payload['mail_password'] ?? null)) {
            $data['mail_password'] = $payload['mail_password'];
        }

        if (filled($payload['ai_api_key'] ?? null)) {
            $data['ai_api_key'] = $payload['ai_api_key'];
        }

        if (filled($payload['postmark_api_key'] ?? null)) {
            $data['postmark_api_key'] = $payload['postmark_api_key'];
        }

        if (filled($payload['resend_api_key'] ?? null)) {
            $data['resend_api_key'] = $payload['resend_api_key'];
        }

        if (filled($payload['sentry_dsn'] ?? null)) {
            $data['sentry_dsn'] = $payload['sentry_dsn'];
        }

        if (filled($payload['slack_bot_token'] ?? null)) {
            $data['slack_bot_token'] = $payload['slack_bot_token'];
        }

        $config->fill($data);
        $config->save();

        $this->integrationConfig->forgetCache();
        $this->integrationConfig->apply();

        return back()->with('status', 'config-updated');
    }
}
