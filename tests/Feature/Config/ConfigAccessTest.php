<?php

namespace Tests\Feature\Config;

use App\Models\AppConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConfigAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'tester', 'guard_name' => 'web']);

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_user_with_list_create_and_update_can_view_and_save(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $user = $this->userWithPermissions([
            'config.list',
            'config.create',
            'config.update',
        ]);

        $this->actingAs($user)->get('/app/config')->assertOk();

        $this->actingAs($user)->put('/app/config', [
            'google_client_id' => 'client-id',
            'google_client_secret' => 'client-secret',
            'google_redirect_uri' => 'https://example.com/callback',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_username' => 'mailer',
            'mail_password' => 'secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Example',
            'ai_provider' => 'openai',
            'ai_api_key' => 'sk-test',
            'ai_model' => 'gpt-4o-mini',
            'registration_enabled' => true,
            'two_factor_enabled' => false,
            'security_csp_enabled' => false,
            'impersonation_timeout_minutes' => 0,
            'hajj_participant_role' => 'user',
            'hajj_participant_role' => 'user',
            'hajj_participant_email_domain' => 'peserta-haji.local',
            'sentry_traces_sample_rate' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('app_configs', [
            'google_client_id' => 'client-id',
            'mail_host' => 'smtp.example.com',
            'ai_provider' => 'openai',
            'ai_model' => 'gpt-4o-mini',
        ]);
    }

    public function test_user_with_list_only_can_view_but_not_save(): void
    {
        $user = $this->userWithPermissions(['config.list']);

        $this->actingAs($user)->get('/app/config')->assertOk();

        $this->actingAs($user)->put('/app/config', [
            'google_client_id' => 'blocked',
        ])->assertForbidden();
    }

    public function test_user_without_any_view_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/app/config')->assertForbidden();
    }

    public function test_secrets_are_not_exposed_in_inertia_response(): void
    {
        AppConfig::singleton()->update([
            'google_client_secret' => 'super-secret',
            'mail_password' => 'mail-secret',
            'ai_api_key' => 'ai-secret',
        ]);

        $user = $this->userWithPermissions(['config.list']);

        $response = $this->actingAs($user)->get('/app/config');

        $response->assertOk();
        $response->assertDontSee('super-secret', false);
        $response->assertDontSee('mail-secret', false);
        $response->assertDontSee('ai-secret', false);
    }

    public function test_empty_secret_fields_keep_existing_values(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $config = AppConfig::singleton();
        $config->update([
            'google_client_secret' => 'keep-me',
            'mail_password' => 'keep-mail',
            'ai_api_key' => 'keep-ai',
        ]);

        $user = $this->userWithPermissions([
            'config.list',
            'config.create',
            'config.update',
        ]);

        $this->actingAs($user)->put('/app/config', [
            'google_client_id' => 'updated-id',
            'google_client_secret' => '',
            'mail_password' => '',
            'ai_api_key' => '',
            'registration_enabled' => true,
            'two_factor_enabled' => false,
            'security_csp_enabled' => false,
            'impersonation_timeout_minutes' => 0,
            'hajj_participant_role' => 'user',
            'hajj_participant_email_domain' => 'peserta-haji.local',
            'sentry_traces_sample_rate' => 0,
        ])->assertSessionHasNoErrors();

        $config->refresh();

        $this->assertSame('updated-id', $config->google_client_id);
        $this->assertSame('keep-me', $config->google_client_secret);
        $this->assertSame('keep-mail', $config->mail_password);
        $this->assertSame('keep-ai', $config->ai_api_key);
    }
}
