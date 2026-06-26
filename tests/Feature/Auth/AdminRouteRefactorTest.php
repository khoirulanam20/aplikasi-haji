<?php

namespace Tests\Feature\Auth;

use App\Models\WebSetting;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteRefactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_superadmin_url_redirects_to_app_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = \App\Models\User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)
            ->get('/superadmin/dashboard')
            ->assertRedirect('/app/dashboard');
    }

    public function test_login_page_includes_theme_css_variables(): void
    {
        WebSetting::query()->create([
            'app_name' => 'Aplikasi Tes',
            'primary_color' => '#AABBCC',
            'secondary_color' => '#112233',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('--color-primary: #AABBCC', false)
            ->assertSee('Aplikasi Tes', false);
    }
}
