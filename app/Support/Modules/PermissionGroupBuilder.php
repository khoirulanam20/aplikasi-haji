<?php

namespace App\Support\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionGroupBuilder
{
    public function build(): Collection
    {
        $permissionsByName = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->keyBy('name');

        $registeredModuleCodes = Module::query()->pluck('code')->all();
        $assigned = collect();
        $moduleGroups = collect();

        $groups = ModuleGroup::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with([
                'modules' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->with('actions'),
            ])
            ->get();

        foreach ($groups as $moduleGroup) {
            $modules = collect();

            foreach ($moduleGroup->modules as $module) {
                $modulePermissions = $this->permissionsForModule($permissionsByName, $module);

                if ($modulePermissions->isEmpty()) {
                    continue;
                }

                $assigned = $assigned->merge($modulePermissions->pluck('name'));
                $modules->push([
                    'label' => $module->title,
                    'code' => $module->code,
                    'permissions' => $modulePermissions->values(),
                ]);
            }

            if ($modules->isEmpty()) {
                continue;
            }

            $moduleGroups->push([
                'name' => $moduleGroup->name,
                'code' => $moduleGroup->code,
                'sort_order' => $moduleGroup->sort_order,
                'modules' => $modules->values(),
                'is_system' => false,
            ]);
        }

        $remaining = $permissionsByName->except($assigned->unique()->all());

        $systemModules = $this->buildSystemModules($remaining, $registeredModuleCodes);

        if ($systemModules->isNotEmpty()) {
            $moduleGroups->push([
                'name' => 'Permission Sistem',
                'code' => 'system',
                'sort_order' => 999999,
                'modules' => $systemModules,
                'is_system' => true,
            ]);
        }

        return $moduleGroups->sortBy('sort_order')->values();
    }

    private function permissionsForModule(Collection $permissionsByName, Module $module): Collection
    {
        $labelsByAction = $module->actions->pluck('label', 'action');

        return $this->sortByAction(
            $this->permissionsForPrefix($permissionsByName, $module->code),
            $labelsByAction,
        );
    }

    private function permissionsForPrefix(Collection $permissionsByName, string $prefix): Collection
    {
        $dotPrefix = $prefix.'.';

        return $permissionsByName->filter(
            fn (Permission $permission, string $name) => str_starts_with($name, $dotPrefix)
        );
    }

    /**
     * @param  Collection<string, Permission>  $remaining
     * @param  list<string>  $registeredModuleCodes
     */
    private function buildSystemModules(Collection $remaining, array $registeredModuleCodes): Collection
    {
        if ($remaining->isEmpty()) {
            return collect();
        }

        return $remaining
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0])
            ->reject(fn (Collection $prefixPermissions, string $prefix) => in_array($prefix, $registeredModuleCodes, true))
            ->map(fn (Collection $prefixPermissions, string $prefix) => [
                'label' => $this->labelForOrphanPrefix($prefix),
                'code' => $prefix,
                'permissions' => $this->sortByAction($prefixPermissions)->values(),
            ])
            ->values();
    }

    /**
     * @param  Collection<string, string|null>  $labelsByAction
     */
    private function sortByAction(Collection $permissions, Collection $labelsByAction = new Collection): Collection
    {
        $actionOrder = array_flip(Module::ACTIONS);

        return $permissions
            ->sortBy(function (Permission $permission) use ($actionOrder) {
                $suffix = (string) str($permission->name)->after('.');

                return $actionOrder[$suffix] ?? 999;
            })
            ->values()
            ->map(function (Permission $permission) use ($labelsByAction) {
                $suffix = (string) str($permission->name)->after('.');

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'display_label' => Module::actionLabel(
                        $suffix,
                        $labelsByAction->get($suffix),
                    ),
                ];
            });
    }

    private function labelForOrphanPrefix(string $prefix): string
    {
        return match ($prefix) {
            'module-groups' => 'Manajemen Grup Modul',
            'impersonate' => 'Impersonate',
            'profile' => 'Profile',
            'notifications' => 'Notifikasi',
            default => str($prefix)->replace(['_', '-'], ' ')->title(),
        };
    }
}
