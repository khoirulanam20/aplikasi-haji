<?php

namespace Tests\Feature\Rbac;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleManagementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_role_and_assign_permissions(): void
    {
        Permission::create(['name' => 'roles.list', 'guard_name' => 'web']);
        Permission::create(['name' => 'roles.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'roles.assign', 'guard_name' => 'web']);
        Permission::create(['name' => 'dashboard.list', 'guard_name' => 'web']);

        $superadmin = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $superadmin->givePermissionTo(['roles.list', 'roles.create', 'roles.assign']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)->post('/app/role-permission/roles', [
            'title' => 'Operator',
            'code' => 'operator',
            'description' => 'Role operator',
            'is_active' => '1',
        ])->assertRedirect();

        $role = Role::where('name', 'operator')->first();
        $this->assertNotNull($role);

        $this->actingAs($user)->put('/app/role-permission/roles/'.$role->id.'/permissions', [
            'permissions' => ['dashboard.list'],
        ])->assertRedirect();

        $this->assertTrue($role->fresh()->hasPermissionTo('dashboard.list'));
    }

    public function test_assigning_permissions_clears_spatie_cache(): void
    {
        Permission::create(['name' => 'roles.list', 'guard_name' => 'web']);
        Permission::create(['name' => 'roles.assign', 'guard_name' => 'web']);
        Permission::create(['name' => 'dashboard.list', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->givePermissionTo(['roles.list', 'roles.assign']);

        $role = Role::create(['name' => 'operator', 'guard_name' => 'web', 'title' => 'Operator', 'is_active' => true]);
        $member = User::factory()->create();
        $member->assignRole($role);

        $this->actingAs($admin)->put('/app/role-permission/roles/'.$role->id.'/permissions', [
            'permissions' => ['dashboard.list'],
        ])->assertRedirect()->assertSessionHas('status', 'permissions-updated');

        $this->assertTrue($member->fresh()->can('dashboard.list'));
    }

    public function test_superadmin_role_permissions_cannot_be_changed_from_ui(): void
    {
        Permission::create(['name' => 'roles.list', 'guard_name' => 'web']);
        Permission::create(['name' => 'roles.assign', 'guard_name' => 'web']);
        Permission::create(['name' => 'dashboard.list', 'guard_name' => 'web']);

        $superadminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $superadminRole->givePermissionTo(['roles.list', 'roles.assign', 'dashboard.list']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)->put('/app/role-permission/roles/'.$superadminRole->id.'/permissions', [
            'permissions' => [],
        ])->assertRedirect()->assertSessionHasErrors('permissions');

        $this->assertTrue($superadminRole->fresh()->hasPermissionTo('dashboard.list'));
    }
}
