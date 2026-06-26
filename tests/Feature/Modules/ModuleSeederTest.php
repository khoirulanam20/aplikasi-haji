<?php

namespace Tests\Feature\Modules;

use App\Models\Menu;
use App\Models\Module;
use App\Models\Role;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_seeder_creates_default_modules_and_menus(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $this->assertDatabaseHas('modules', ['code' => 'dashboard']);
        $this->assertDatabaseHas('modules', ['code' => 'users']);
        $this->assertDatabaseHas('modules', ['code' => 'websetting']);

        $this->assertDatabaseHas('menus', [
            'domain' => 'app',
            'route_name' => 'app.dashboard',
            'permission_name' => 'dashboard.list',
        ]);

        $superadmin = Role::where('name', 'superadmin')->first();
        $this->assertTrue($superadmin->hasPermissionTo('users.list'));
        $this->assertTrue($superadmin->hasPermissionTo('roles.assign'));
    }

    public function test_superadmin_menus_not_duplicated_from_role_seeder(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $dashboardMenus = Menu::query()
            ->where('domain', 'app')
            ->where('route_name', 'app.dashboard')
            ->count();

        $this->assertSame(1, $dashboardMenus);
    }
}
