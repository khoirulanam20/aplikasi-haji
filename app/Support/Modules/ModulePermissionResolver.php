<?php

namespace App\Support\Modules;

use App\Models\Module;

class ModulePermissionResolver
{
    /**
     * Resolve permission name from a registered module route (e.g. app.api-config.index + list).
     */
    public function forRoute(string $routeName, string $action): string
    {
        $resolved = resolve_admin_route_name($routeName) ?? $routeName;

        $module = Module::query()
            ->where(function ($query) use ($resolved, $routeName): void {
                $query->where('route_name', $resolved)
                    ->orWhere('route_name', $routeName);
            })
            ->first();

        if ($module) {
            return $module->permissionNameForAction($action);
        }

        $canonicalCode = ModuleConventions::requiredCodeForRoute($resolved)
            ?? ModuleConventions::requiredCodeForRoute($routeName);

        if ($canonicalCode !== null) {
            return $canonicalCode.'.'.$action;
        }

        return 'unknown.'.$action;
    }

    /**
     * @param  list<string>  $actions
     * @return list<string>
     */
    public function aliasesForRoute(string $routeName, string ...$actions): array
    {
        $names = [];

        foreach ($actions as $action) {
            $names[] = $this->forRoute($routeName, $action);
        }

        return array_values(array_unique($names));
    }
}
