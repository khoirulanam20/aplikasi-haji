<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Models\User;
use App\Support\Modules\ModuleOrderService;
use App\Support\Navigation\MenuBuilder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ModuleOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_group_gets_sort_order_after_existing(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $this->actingAs($this->superadminUser())
            ->post('/app/module-groups', [
                'name' => 'Grup Baru',
                'code' => 'grup_baru',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $group = ModuleGroup::where('code', 'grup_baru')->first();
        $maxBefore = ModuleGroup::where('code', '!=', 'grup_baru')->max('sort_order');

        $this->assertGreaterThan($maxBefore, $group->sort_order);
    }

    public function test_reorder_groups_updates_sidebar_order(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $umum = ModuleGroup::where('code', 'umum')->first();
        $data = ModuleGroup::where('code', 'data')->first();

        app(ModuleOrderService::class)->reorderGroups([$data->id, $umum->id]);

        $this->actingAs($this->superadminUser());

        $navigation = app(MenuBuilder::class)->groupedForUserDomain('app');

        $this->assertSame('Data', $navigation['groups']->first()['name']);
    }

    public function test_reorder_modules_within_group_updates_sort_order(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $group = ModuleGroup::where('code', 'pengaturan_pengguna')->first();
        $modules = Module::where('module_group_id', $group->id)->orderBy('sort_order')->get();
        $reversed = $modules->pluck('id')->reverse()->values()->all();

        app(ModuleOrderService::class)->reorderModules($group->id, $reversed);

        $sorted = Module::where('module_group_id', $group->id)->orderBy('sort_order')->pluck('id')->all();

        $this->assertSame($reversed, $sorted);
    }

    public function test_superadmin_can_reorder_via_api(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        Permission::firstOrCreate(['name' => 'module-groups.update', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'modules.update', 'guard_name' => 'web']);

        $groups = ModuleGroup::orderBy('sort_order')->pluck('id')->all();

        $this->actingAs($this->superadminUser())
            ->putJson('/app/module-groups/reorder', ['ids' => array_reverse($groups)])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_modules_reorder_endpoint_is_not_captured_by_module_route(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        Permission::firstOrCreate(['name' => 'modules.update', 'guard_name' => 'web']);

        $group = ModuleGroup::first();
        $ids = Module::where('module_group_id', $group->id)->orderBy('sort_order')->pluck('id')->all();

        $this->actingAs($this->superadminUser())
            ->putJson('/app/modules/reorder', [
                'module_group_id' => $group->id,
                'ids' => array_reverse($ids),
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    private function superadminUser(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['title' => 'Superadmin', 'is_active' => true]
        );
        $role->givePermissionTo(Permission::all());

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }
}
