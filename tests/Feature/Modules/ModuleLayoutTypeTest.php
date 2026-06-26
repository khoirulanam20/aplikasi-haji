<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleLayoutTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_store_persists_form_base_layout_and_actions(): void
    {
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'modules.create', 'guard_name' => 'web']);
        $role->givePermissionTo('modules.create');

        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $group = ModuleGroup::create([
            'name' => 'Grup Tes',
            'code' => 'grup_tes',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Pengaturan Tes',
            'code' => 'pengaturan_tes',
            'layout_type' => Module::LAYOUT_FORM_BASE,
            'route_name' => 'app.dashboard',
            'show_in_sidebar' => false,
            'is_active' => true,
            'enabled_actions' => Module::actionPreset(Module::LAYOUT_FORM_BASE),
        ]);

        $response->assertRedirect();

        $module = Module::where('code', 'pengaturan_tes')->first();

        $this->assertNotNull($module);
        $this->assertSame(Module::LAYOUT_FORM_BASE, $module->layout_type);
        $this->assertEqualsCanonicalizing(
            Module::actionPreset(Module::LAYOUT_FORM_BASE),
            $module->actions()->where('is_enabled', true)->pluck('action')->all()
        );
    }

    public function test_action_preset_returns_expected_actions_per_layout(): void
    {
        $this->assertEquals(
            ['list', 'read', 'create', 'update'],
            Module::actionPreset(Module::LAYOUT_FORM_BASE)
        );

        $this->assertEquals(
            ['list', 'show', 'create', 'edit', 'update', 'delete'],
            Module::actionPreset(Module::LAYOUT_TABLE_BASE)
        );
    }
}
