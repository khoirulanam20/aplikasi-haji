<?php

namespace Tests\Feature\Impersonate;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersImpersonateActionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'tester', 'guard_name' => 'web']);

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_users_page_shows_impersonate_action_when_allowed(): void
    {
        $admin = $this->userWithPermissions(['users.list', 'impersonate.start']);
        $target = User::factory()->create(['is_active' => true, 'name' => 'Target User']);

        $this->actingAs($admin)
            ->get('/app/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 2));
    }

    public function test_users_page_hides_impersonate_action_without_permission(): void
    {
        $admin = $this->userWithPermissions(['users.list']);

        User::factory()->create(['is_active' => true, 'name' => 'Target User']);

        $this->actingAs($admin)
            ->get('/app/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));
    }
}
