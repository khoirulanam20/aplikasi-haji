<?php

namespace Tests\Feature\Auth;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Models\User;
use App\Support\Modules\ModuleRegistryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_role_is_redirected_to_access_pending(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('access.pending'));
    }

    public function test_registration_does_not_assign_default_role(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->post('/register', [
            'name' => 'User Baru',
            'email' => 'baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'baru@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->roles()->exists());
    }

    public function test_custom_role_with_module_permission_uses_app_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $group = ModuleGroup::query()->create([
            'name' => 'Umum',
            'code' => 'umum',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $module = Module::query()->create([
            'module_group_id' => $group->id,
            'title' => 'Dashboard',
            'code' => 'dashboard',
            'route_name' => 'app.dashboard',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list']);

        $role = Role::create([
            'name' => 'role_test',
            'guard_name' => 'web',
            'title' => 'Role test',
            'is_active' => true,
        ]);
        $role->syncPermissions(['dashboard.list']);

        $user = User::factory()->create();
        $user->assignRole('role_test');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('app.dashboard'));

        $this->actingAs($user)
            ->get('/app/dashboard')
            ->assertOk();
    }

    public function test_login_without_role_does_not_hit_forbidden_on_dashboard_chain(): void
    {
        User::create([
            'name' => 'Tanpa Role',
            'email' => 'norole@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'login' => 'norole@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')->assertRedirect(route('access.pending'));
        $this->get('/access/pending')->assertOk();
    }
}
