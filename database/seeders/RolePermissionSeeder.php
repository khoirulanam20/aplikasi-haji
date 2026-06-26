<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $systemPermissions = [
            'profile.read',
            'profile.update',
            'notifications.list',
            'notifications.read',
            'module-groups.create',
            'module-groups.update',
            'module-groups.delete',
            'impersonate.start',
            'impersonate.stop',
        ];

        foreach ($systemPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superadmin = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['title' => 'Superadmin', 'description' => 'Akses penuh sistem', 'is_active' => true]
        );

        $superadmin->update([
            'title' => 'Superadmin',
            'description' => 'Akses penuh sistem',
            'is_active' => true,
        ]);

        $superadmin->givePermissionTo($systemPermissions);

        $menus = [
            ['domain' => 'shared', 'label' => 'Profile', 'route_name' => 'profile.edit', 'icon' => '<i class="iconoir-user"></i>', 'sort_order' => 90, 'permission_name' => 'profile.read'],
            ['domain' => 'shared', 'label' => 'Notifications', 'route_name' => 'notifications.index', 'icon' => '<i class="iconoir-bell"></i>', 'sort_order' => 91, 'permission_name' => 'notifications.list'],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['domain' => $menu['domain'], 'route_name' => $menu['route_name']],
                $menu
            );
        }

        Menu::query()->where('domain', 'admin_desa')->delete();

        $legacyRole = Role::query()->where('name', 'admin_desa')->where('guard_name', 'web')->first();
        if ($legacyRole) {
            $legacyRole->users()->detach();
            $legacyRole->permissions()->detach();
            $legacyRole->delete();
        }
    }
}
