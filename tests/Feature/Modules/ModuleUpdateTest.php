<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class ModuleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_can_be_updated_via_put(): void
    {
        Permission::create(['name' => 'modules.list', 'guard_name' => 'web']);
        Permission::create(['name' => 'modules.update', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo(['modules.list', 'modules.update']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $group = ModuleGroup::create(['name' => 'Data', 'code' => 'data', 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Master Data',
            'code' => 'masterdata',
            'route_name' => 'app.master-data.index',
            'sort_order' => 10,
            'is_active' => true,
            'show_in_sidebar' => true,
            'layout_type' => Module::LAYOUT_TABLE_BASE,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list', 'create']);

        $response = $this->actingAs($user)->put('/app/modules/'.$module->id, [
            '_method' => 'PUT',
            'module_group_id' => $group->id,
            'title' => 'Master Data Diubah',
            'code' => 'masterdata',
            'route_name' => 'app.master-data.index',
            'description' => 'Deskripsi baru',
            'sort_order' => 10,
            'is_active' => '1',
            'show_in_sidebar' => '1',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'enabled_actions' => ['list', 'create', 'edit', 'update', 'delete'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $module->refresh();

        $this->assertSame('Master Data Diubah', $module->title);
        $this->assertSame('Deskripsi baru', $module->description);
        $this->assertTrue($module->actions()->where('action', 'edit')->where('is_enabled', true)->exists());
        $this->assertDatabaseHas('permissions', ['name' => 'masterdata.edit']);
    }
}
