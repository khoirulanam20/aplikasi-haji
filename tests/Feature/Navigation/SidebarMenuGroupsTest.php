<?php

namespace Tests\Feature\Navigation;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Navigation\MenuBuilder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarMenuGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_grouped_navigation_organizes_superadmin_menus_by_module_group(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $navigation = app(MenuBuilder::class)->groupedForUserDomain('app');

        $this->assertGreaterThan(0, $navigation['groups']->count());
        $this->assertSame('Pengaturan Pengguna', $navigation['groups']->firstWhere('name', 'Pengaturan Pengguna')['name']);

        $penggunaGroup = $navigation['groups']->firstWhere('name', 'Pengaturan Pengguna');
        $this->assertNotNull($penggunaGroup);
        $this->assertTrue(
            $penggunaGroup['menus']->contains(fn ($menu) => $menu->route_name === 'app.users.index')
        );
    }

    public function test_orphan_superadmin_menu_not_shown_in_sidebar(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        Menu::create([
            'domain' => 'app',
            'label' => 'Menu Lama',
            'route_name' => 'app.legacy.index',
            'sort_order' => 5,
            'permission_name' => 'dashboard.list',
        ]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $menus = app(MenuBuilder::class)->forUserDomain('app');

        $this->assertFalse($menus->contains(fn ($menu) => $menu->route_name === 'app.legacy.index'));

        $this->get('/app/dashboard')
            ->assertOk()
            ->assertDontSee('Menu Lama');
    }

    public function test_module_without_route_name_not_in_sidebar(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $group = ModuleGroup::create(['name' => 'Tes', 'code' => 'tes_sidebar', 'sort_order' => 5, 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Modul Tes',
            'code' => 'tes_no_route',
            'route_name' => null,
            'show_in_sidebar' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        app(\App\Support\Modules\ModuleRegistryService::class)->syncActions($module, ['list']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $navigation = app(MenuBuilder::class)->groupedForUserDomain('app');

        $this->assertFalse(
            $navigation['groups']->flatMap(fn ($g) => $g['menus'])->contains(fn ($menu) => $menu->label === 'Modul Tes')
        );
    }

    public function test_shared_profile_and_notifications_not_in_sidebar(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $menus = app(MenuBuilder::class)->forUserDomain('app');
        $navigation = app(MenuBuilder::class)->groupedForUserDomain('app');

        $this->assertFalse($menus->contains(fn ($menu) => $menu->route_name === 'profile.edit'));
        $this->assertFalse($menus->contains(fn ($menu) => $menu->route_name === 'notifications.index'));
        $this->assertTrue($navigation['ungrouped']->isEmpty());
    }

    public function test_superadmin_layout_renders_module_group_labels(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)
            ->get('/app/dashboard')
            ->assertOk()
            ->assertSee('Pengaturan Pengguna')
            ->assertSee('Pengaturan Website');
    }
}
