<?php

namespace Tests\Feature\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_user(): void
    {
        $this->seedSuperadmin();

        $response = $this->actingAs($this->superadminUser())->post('/app/users', [
            'name' => 'Operator Baru',
            'username' => 'operator',
            'email' => 'operator@example.com',
            'password' => 'password123',
            'role' => 'operator',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'operator@example.com']);
    }

    public function test_superadmin_can_update_and_delete_user(): void
    {
        $this->seedSuperadmin();
        $admin = $this->superadminUser();
        $target = User::factory()->create(['email' => 'target@example.com']);
        $target->assignRole('operator');

        $this->actingAs($admin)->put('/app/users/'.$target->id, [
            'name' => 'Target Updated',
            'username' => 'target',
            'email' => 'target@example.com',
            'password' => '',
            'role' => 'operator',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Target Updated']);

        $this->actingAs($admin)->delete('/app/users/'.$target->id)->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    private function seedSuperadmin(): void
    {
        Permission::create(['name' => 'users.list', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.update', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.delete', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        Role::create(['name' => 'operator', 'guard_name' => 'web', 'title' => 'Operator', 'is_active' => true]);
        $role->givePermissionTo(['users.list', 'users.create', 'users.update', 'users.delete']);
    }

    private function superadminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }
}
