<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
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

    public function test_user_without_profile_read_cannot_open_profile_page(): void
    {
        $user = $this->userWithPermissions(['notifications.list']);

        $this->actingAs($user)->get('/profile')->assertForbidden();
    }

    public function test_user_with_profile_read_only_cannot_update_profile(): void
    {
        $user = $this->userWithPermissions(['profile.read']);

        $this->actingAs($user)->get('/profile')->assertOk();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Changed',
            'email' => $user->email,
        ])->assertForbidden();
    }

    public function test_user_with_profile_update_can_save_profile(): void
    {
        $user = $this->userWithPermissions(['profile.read', 'profile.update']);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Changed Name',
                'email' => $user->email,
            ])
            ->assertRedirect('/profile');

        $this->assertSame('Changed Name', $user->fresh()->name);
    }

    public function test_profile_read_only_hides_save_button(): void
    {
        $user = $this->userWithPermissions(['profile.read']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Save', false);
    }
}
