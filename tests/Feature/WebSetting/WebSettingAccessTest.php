<?php

namespace Tests\Feature\WebSetting;

use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebSettingAccessTest extends TestCase
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
        $user = $this->userWithPermissions([
            'websetting.list',
            'websetting.create',
            'websetting.update',
        ]);

        $this->actingAs($user)->get('/app/web-setting')->assertOk();

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Starter PEBEJE',
            'app_tagline' => 'Tagline',
            'site_description' => 'Deskripsi situs',
            'primary_color' => '#112233',
            'secondary_color' => '#AABBCC',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('web_settings', [
            'app_name' => 'Starter PEBEJE',
            'primary_color' => '#112233',
        ]);
    }

    public function test_user_with_list_only_can_view_but_not_save(): void
    {
        $user = $this->userWithPermissions(['websetting.list']);

        $this->actingAs($user)->get('/app/web-setting')->assertOk();

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Blocked',
            'primary_color' => '#112233',
            'secondary_color' => '#AABBCC',
        ])->assertForbidden();
    }

    public function test_user_with_read_only_can_view_but_not_save(): void
    {
        $user = $this->userWithPermissions(['websetting.read']);

        $this->actingAs($user)->get('/app/web-setting')->assertOk();

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Blocked',
            'primary_color' => '#112233',
            'secondary_color' => '#AABBCC',
        ])->assertForbidden();
    }

    public function test_user_with_update_only_cannot_view_or_save(): void
    {
        $user = $this->userWithPermissions(['websetting.update']);

        $this->actingAs($user)->get('/app/web-setting')->assertForbidden();

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Blocked',
            'primary_color' => '#112233',
            'secondary_color' => '#AABBCC',
        ])->assertForbidden();
    }

    public function test_user_with_create_without_update_cannot_save(): void
    {
        $user = $this->userWithPermissions(['websetting.list', 'websetting.create']);

        $this->actingAs($user)->get('/app/web-setting')->assertOk();

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Blocked',
            'primary_color' => '#112233',
            'secondary_color' => '#AABBCC',
        ])->assertForbidden();
    }

    public function test_user_without_any_view_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/app/web-setting')->assertForbidden();
    }

    public function test_logo_upload_is_stored_as_webp_when_allowed_to_save(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions([
            'websetting.list',
            'websetting.create',
            'websetting.update',
        ]);

        $this->actingAs($user)->put('/app/web-setting', [
            'app_name' => 'Starter PEBEJE',
            'primary_color' => '#FF5B37',
            'secondary_color' => '#4B5694',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $setting = WebSetting::first();
        $this->assertNotNull($setting?->logo_path);
        $this->assertStringEndsWith('.webp', $setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }

    public function test_theme_colors_are_injected_in_layout(): void
    {
        WebSetting::query()->create([
            'app_name' => 'Tema Test',
            'primary_color' => '#ABCDEF',
            'secondary_color' => '#123456',
        ]);

        $user = $this->userWithPermissions(['websetting.list']);

        $this->actingAs($user)
            ->get('/app/web-setting')
            ->assertOk()
            ->assertSee('--color-primary: #ABCDEF', false)
            ->assertSee('--color-secondary: #123456', false);
    }

    public function test_read_only_user_does_not_see_save_button(): void
    {
        $user = $this->userWithPermissions(['websetting.read']);

        $this->actingAs($user)
            ->get('/app/web-setting')
            ->assertOk()
            ->assertDontSee('Simpan', false);
    }
}
