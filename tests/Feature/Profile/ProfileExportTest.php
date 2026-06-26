<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['profile.read', 'profile.update'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_profile_export_returns_json_attachment(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['profile.read', 'profile.update']);

        $response = $this->actingAs($user)->get('/profile/export');

        $response->assertOk()
            ->assertHeader(
                'content-disposition',
                'attachment; filename="profile-export-'.$user->id.'.json"',
            )
            ->assertJsonStructure([
                'exported_at',
                'profile' => ['id', 'name', 'username', 'email', 'is_active', 'roles', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('profile.id', $user->id)
            ->assertJsonPath('profile.email', $user->email);
    }

    public function test_profile_export_requires_authentication(): void
    {
        $this->get('/profile/export')->assertRedirect('/login');
    }
}
