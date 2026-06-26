<?php

namespace Tests\Feature\Config;

use App\Models\AppConfig;
use App\Models\User;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationConfigTest extends TestCase
{
    use RefreshDatabase;

    private function configAdmin(): User
    {
        $role = Role::create(['name' => 'config-admin', 'guard_name' => 'web']);

        foreach (['config.list', 'config.create', 'config.update'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->syncPermissions(['config.list', 'config.create', 'config.update']);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(): array
    {
        return [
            'registration_enabled' => '1',
            'two_factor_enabled' => '0',
            'security_csp_enabled' => '0',
            'impersonation_timeout_minutes' => 0,
            'hajj_participant_role' => 'user',
            'hajj_participant_email_domain' => 'peserta-haji.local',
            'sentry_environment' => '',
            'sentry_traces_sample_rate' => 0,
            'slack_bot_channel' => '',
        ];
    }

    public function test_registration_disabled_via_config_blocks_register_page(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $admin = $this->configAdmin();

        $this->actingAs($admin)->put('/app/config', array_merge($this->basePayload(), [
            'registration_enabled' => '0',
        ]))->assertSessionHasNoErrors()->assertRedirect();

        app(IntegrationConfigService::class)->forgetCache();
        Cache::forget('app_config_applied');
        app(IntegrationConfigService::class)->apply();

        auth()->logout();

        $this->get('/register')->assertNotFound();
    }

    public function test_invalid_hajj_participant_role_is_rejected(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $admin = $this->configAdmin();

        $this->actingAs($admin)->put('/app/config', array_merge($this->basePayload(), [
            'hajj_participant_role' => 'nonexistent-role',
        ]))->assertSessionHasErrors('hajj_participant_role');
    }

    public function test_application_settings_persist_to_database(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $admin = $this->configAdmin();

        $response = $this->actingAs($admin)->put('/app/config', array_merge($this->basePayload(), [
            'hajj_participant_email_domain' => 'haji.temanggung.go.id',
            'two_factor_enabled' => '1',
        ]));

        $response->assertSessionHasNoErrors()->assertRedirect();
        $response->assertSessionHas('status', 'config-updated');

        $config = AppConfig::singleton()->fresh();
        $this->assertSame('haji.temanggung.go.id', $config->hajj_participant_email_domain);
        $this->assertTrue($config->two_factor_enabled);

        $this->assertDatabaseHas('app_configs', [
            'hajj_participant_email_domain' => 'haji.temanggung.go.id',
            'two_factor_enabled' => 1,
        ]);
    }
}
