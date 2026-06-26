<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleIconValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_icon_html_is_rejected(): void
    {
        $this->seedModulePermissions();
        $user = $this->superadminUser();
        $group = ModuleGroup::query()->create([
            'name' => 'Grup',
            'code' => 'grup',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Modul Test',
            'code' => 'modul_test',
            'icon' => '<script>alert(1)</script>',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'enabled_actions' => ['list'],
        ]);

        $response->assertSessionHasErrors('icon');
    }

    public function test_valid_iconoir_icon_is_accepted(): void
    {
        $this->seedModulePermissions();
        $user = $this->superadminUser();
        $group = ModuleGroup::query()->create([
            'name' => 'Grup',
            'code' => 'grup',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Modul Test',
            'code' => 'modul_test',
            'icon' => '<i class="iconoir-settings"></i>',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'enabled_actions' => ['list'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('modules', [
            'code' => 'modul_test',
            'icon' => '<i class="iconoir-settings"></i>',
        ]);
    }

    public function test_iconoir_config_lists_curated_icons(): void
    {
        $icons = config('iconoir.icons', []);

        $this->assertNotEmpty($icons);
        $this->assertArrayHasKey('name', $icons[0]);
        $this->assertArrayHasKey('label', $icons[0]);
    }

    private function seedModulePermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        foreach (['modules.list', 'modules.create', 'modules.update', 'module-groups.update'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        $role->givePermissionTo(['modules.list', 'modules.create', 'modules.update', 'module-groups.update']);
    }

    private function superadminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }
}
