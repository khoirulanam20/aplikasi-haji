<?php

namespace App\Support\Modules;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleAction;
use App\Models\Role;
use App\Support\Navigation\SidebarMenuCacheInvalidator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ModuleRegistryService
{
    public function __construct(private readonly SidebarMenuCacheInvalidator $menuCache)
    {
    }

    public function syncModule(Module $module): void
    {
        $module->load(['actions', 'group']);

        DB::transaction(function () use ($module): void {
            $desired = collect($module->permissionNames());

            Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'like', $module->code.'.%')
                ->get()
                ->each(function (Permission $permission) use ($desired): void {
                    if (! $desired->contains($permission->name)) {
                        $permission->delete();
                    }
                });

            foreach ($desired as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }

            $superadmin = Role::query()
                ->where('name', 'superadmin')
                ->where('guard_name', 'web')
                ->first();

            if ($superadmin && $desired->isNotEmpty()) {
                $superadmin->givePermissionTo($desired->all());
            }

            if ($module->show_in_sidebar && $module->is_active && $module->route_name) {
                Menu::updateOrCreate(
                    [
                        'domain' => config('starterkit.admin_menu_domain', 'app'),
                        'route_name' => $module->route_name,
                    ],
                    [
                        'label' => $module->title,
                        'icon' => $module->icon,
                        'sort_order' => $module->sort_order ?? 0,
                        'permission_name' => $module->sidebarPermissionName(),
                    ]
                );
            } else {
                Menu::query()
                    ->where('domain', config('starterkit.admin_menu_domain', 'app'))
                    ->where('route_name', $module->route_name)
                    ->delete();
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        $this->menuCache->invalidate();
    }

    public function removePermissionsByPrefix(string $prefix): int
    {
        $deleted = 0;

        DB::transaction(function () use ($prefix, &$deleted): void {
            $deleted = Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'like', $prefix.'.%')
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        $this->menuCache->invalidate();

        return $deleted;
    }

    public function removeModule(Module $module): void
    {
        DB::transaction(function () use ($module): void {
            $this->removePermissionsByPrefix($module->code);

            if ($module->route_name) {
                Menu::query()
                    ->where('domain', config('starterkit.admin_menu_domain', 'app'))
                    ->where('route_name', $module->route_name)
                    ->delete();
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        $this->menuCache->invalidate();
    }

    /**
     * @param  list<string>  $enabledActions
     * @param  list<array{action: string, label: string}>  $customActions
     */
    public function syncActions(Module $module, array $enabledActions, array $customActions = []): void
    {
        $enabledActions = array_values(array_unique($enabledActions));
        $customByAction = collect($customActions)->keyBy('action');
        $customSlugs = $customByAction->keys()->all();

        foreach (Module::ACTIONS as $action) {
            ModuleAction::updateOrCreate(
                ['module_id' => $module->id, 'action' => $action],
                ['is_enabled' => in_array($action, $enabledActions, true), 'label' => null]
            );
        }

        foreach ($customByAction as $action => $definition) {
            ModuleAction::updateOrCreate(
                ['module_id' => $module->id, 'action' => (string) $action],
                [
                    'is_enabled' => in_array($action, $enabledActions, true),
                    'label' => $definition['label'] ?? null,
                ]
            );
        }

        ModuleAction::query()
            ->where('module_id', $module->id)
            ->whereNotIn('action', array_merge(Module::ACTIONS, $customSlugs))
            ->delete();

        $module->load('actions');
        $this->syncModule($module);
    }
}
