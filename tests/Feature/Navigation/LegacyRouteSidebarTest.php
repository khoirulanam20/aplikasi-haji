<?php

namespace Tests\Feature\Navigation;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Navigation\MenuBuilder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LegacyRouteSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_shows_modules_with_legacy_superadmin_route_names(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Permission::firstOrCreate(['name' => 'dashboard.list', 'guard_name' => 'web']);

        $group = ModuleGroup::query()->create([
            'name' => 'Umum',
            'code' => 'umum',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Module::query()->create([
            'module_group_id' => $group->id,
            'title' => 'Dashboard',
            'code' => 'dashboard',
            'route_name' => 'superadmin.dashboard',
            'show_in_sidebar' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $navigation = app(MenuBuilder::class)->groupedForUserDomain('app');

        $this->assertTrue(
            $navigation['groups']->flatMap(fn (array $group) => $group['menus'])
                ->contains(fn ($menu) => $menu->route_name === 'app.dashboard'),
        );
    }
}
