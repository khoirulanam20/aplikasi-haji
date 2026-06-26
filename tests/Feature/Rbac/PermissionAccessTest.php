<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_gets_forbidden(): void
    {
        $role = Role::create(['name' => 'operator', 'guard_name' => 'web']);
        Permission::create(['name' => 'dashboard.list']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/app/master-data');

        $response->assertStatus(403);
    }
}
