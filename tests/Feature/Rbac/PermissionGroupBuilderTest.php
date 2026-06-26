<?php

namespace Tests\Feature\Rbac;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Support\Modules\ModuleRegistryService;
use App\Support\Modules\PermissionGroupBuilder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionGroupBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_are_nested_under_module_groups_and_modules(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $group = ModuleGroup::create(['name' => 'Grup Tes', 'code' => 'grup_tes', 'sort_order' => 99, 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Modul Tes',
            'code' => 'tes',
            'route_name' => 'app.dashboard',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list', 'create']);

        $groups = app(PermissionGroupBuilder::class)->build();

        $tesModuleGroup = $groups->firstWhere('code', 'grup_tes');

        $this->assertNotNull($tesModuleGroup);
        $this->assertSame('Grup Tes', $tesModuleGroup['name']);
        $this->assertCount(1, $tesModuleGroup['modules']);

        $tesModule = $tesModuleGroup['modules']->first();
        $this->assertSame('Modul Tes', $tesModule['label']);
        $this->assertSame('tes', $tesModule['code']);
        $this->assertTrue(
            $tesModule['permissions']->contains(fn (array $permission) => $permission['name'] === 'tes.list')
        );
        $this->assertSame('List', $tesModule['permissions']->firstWhere('name', 'tes.list')['display_label']);
    }

    public function test_system_permissions_are_grouped_separately(): void
    {
        $this->seed(RolePermissionSeeder::class);

        Permission::firstOrCreate(['name' => 'profile.read', 'guard_name' => 'web']);

        $groups = app(PermissionGroupBuilder::class)->build();

        $systemGroup = $groups->firstWhere('code', 'system');

        $this->assertNotNull($systemGroup);
        $this->assertSame('Permission Sistem', $systemGroup['name']);
        $this->assertTrue($systemGroup['is_system']);
    }

    public function test_orphan_permissions_appear_in_system_group(): void
    {
        Permission::firstOrCreate(['name' => 'orphan_mod.list', 'guard_name' => 'web']);

        $groups = app(PermissionGroupBuilder::class)->build();

        $systemGroup = $groups->firstWhere('code', 'system');
        $this->assertNotNull($systemGroup);
        $this->assertTrue(
            collect($systemGroup['modules'])->pluck('code')->contains('orphan_mod')
        );
    }
}
