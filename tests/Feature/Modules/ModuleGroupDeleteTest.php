<?php

namespace Tests\Feature\Modules;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleGroupDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_delete_module_group_and_cleanup_registry(): void
    {
        Permission::firstOrCreate(['name' => 'module-groups.delete', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo('module-groups.delete');

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $group = ModuleGroup::query()->create([
            'name' => 'Grup Hapus',
            'code' => 'grup_hapus',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $module = Module::query()->create([
            'module_group_id' => $group->id,
            'title' => 'Modul Hapus',
            'code' => 'modul_hapus',
            'route_name' => 'app.dashboard',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list']);

        $this->assertDatabaseHas('permissions', ['name' => 'modul_hapus.list']);

        $response = $this->actingAs($user)->delete('/app/module-groups/'.$group->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('module_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('modules', ['id' => $module->id]);
        $this->assertDatabaseMissing('permissions', ['name' => 'modul_hapus.list']);
        $this->assertDatabaseMissing('menus', [
            'route_name' => 'app.dashboard',
            'permission_name' => 'modul_hapus.list',
        ]);
    }
}
