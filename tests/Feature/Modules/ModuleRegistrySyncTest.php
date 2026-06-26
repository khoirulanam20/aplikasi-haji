<?php

namespace Tests\Feature\Modules;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class ModuleRegistrySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_sync_creates_permissions_and_menu(): void
    {
        $group = ModuleGroup::create(['name' => 'Master', 'code' => 'master', 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Contoh Modul',
            'code' => 'contoh_modul',
            'route_name' => 'app.contoh.index',
            'icon' => '<i class="iconoir-app-window"></i>',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list', 'create']);

        $this->assertDatabaseHas('permissions', ['name' => 'contoh_modul.list']);
        $this->assertDatabaseHas('permissions', ['name' => 'contoh_modul.create']);
        $this->assertDatabaseHas('menus', ['route_name' => 'app.contoh.index', 'permission_name' => 'contoh_modul.list']);
    }

    public function test_new_module_permissions_are_granted_to_superadmin_role(): void
    {
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $group = ModuleGroup::create(['name' => 'Tes', 'code' => 'tes_grup', 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Modul Tes',
            'code' => 'tes',
            'route_name' => 'app.dashboard',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list']);

        $this->assertTrue($role->fresh()->hasPermissionTo('tes.list'));
    }

    public function test_superadmin_can_access_modules_page(): void
    {
        Permission::create(['name' => 'modules.list', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo('modules.list');
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)->get('/app/modules')->assertOk();
    }
}
