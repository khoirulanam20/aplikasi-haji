<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class ModuleCustomActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_can_store_custom_rbac_action(): void
    {
        Permission::create(['name' => 'modules.create', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo('modules.create');

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $group = ModuleGroup::create(['name' => 'Data', 'code' => 'data', 'is_active' => true]);

        $response = $this->actingAs($user)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Laporan',
            'code' => 'laporan',
            'route_name' => 'app.dashboard',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'is_active' => true,
            'show_in_sidebar' => false,
            'enabled_actions' => ['list', 'export_pdf'],
            'custom_actions' => [
                ['action' => 'export_pdf', 'label' => 'Export PDF'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $module = Module::where('code', 'laporan')->first();
        $this->assertNotNull($module);

        $this->assertDatabaseHas('module_actions', [
            'module_id' => $module->id,
            'action' => 'export_pdf',
            'label' => 'Export PDF',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('permissions', ['name' => 'laporan.export_pdf']);
    }
}
