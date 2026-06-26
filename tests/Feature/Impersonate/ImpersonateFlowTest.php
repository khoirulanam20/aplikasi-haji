<?php

namespace Tests\Feature\Impersonate;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImpersonateFlowTest extends TestCase
{
    use RefreshDatabase;

    private function grantImpersonateStart(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'impersonator', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'impersonate.start', 'guard_name' => 'web']);
        $role->givePermissionTo('impersonate.start');
        $user->assignRole($role);
    }

    public function test_superadmin_can_start_and_stop_impersonation(): void
    {
        $admin = User::factory()->create();
        $this->grantImpersonateStart($admin);

        $target = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post("/app/impersonate/{$target->id}")->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($target);
        $this->assertNotNull(session('impersonator_id'));

        $this->post('/impersonate/stop')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_without_permission_cannot_start_impersonation(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->post("/app/impersonate/{$target->id}")->assertForbidden();
    }

    public function test_cannot_impersonate_inactive_user(): void
    {
        $admin = User::factory()->create();
        $this->grantImpersonateStart($admin);

        $target = User::factory()->create(['is_active' => false]);

        $this->actingAs($admin)->post("/app/impersonate/{$target->id}")->assertStatus(422);
    }

    public function test_cannot_impersonate_superadmin_user(): void
    {
        $admin = User::factory()->create();
        $this->grantImpersonateStart($admin);

        $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole($superadminRole);

        $this->actingAs($admin)->post("/app/impersonate/{$target->id}")->assertStatus(422);
    }
}
