<?php

namespace App\Support\Navigation;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class MenuBuilder
{
    public function forUserDomain(?string $domain): Collection
    {
        $navigation = $this->groupedForUserDomain($domain);

        return $navigation['groups']
            ->flatMap(fn (array $group) => $group['menus'])
            ->merge($navigation['ungrouped'])
            ->values();
    }

    /**
     * @return array{groups: Collection<int, array{name: string, sort_order: int, menus: Collection<int, Menu>}>, ungrouped: Collection<int, Menu>}
     */
    public function groupedForUserDomain(?string $domain): array
    {
        if ($domain === config('starterkit.admin_menu_domain', 'app')) {
            return $this->groupedForSuperadmin();
        }

        return $this->groupedFromMenusTable($domain);
    }

    /**
     * @return array{groups: Collection<int, array{name: string, sort_order: int, menus: Collection<int, Menu>}>, ungrouped: Collection<int, Menu>}
     */
    private function groupedForSuperadmin(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'groups' => collect(),
                'ungrouped' => collect(),
            ];
        }

        $userPermissionNames = $user->getAllPermissions()->pluck('name')->toArray();

        $groups = ModuleGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['modules' => fn ($query) => $query->visibleInSidebar()->orderBy('sort_order')->orderBy('title')])
            ->get();

        $navigationGroups = $groups->map(function (ModuleGroup $group) use ($userPermissionNames) {
            $menus = $group->modules
                ->filter(function (Module $module) use ($userPermissionNames) {
                    $permission = $module->sidebarPermissionName();

                    return $permission === null || in_array($permission, $userPermissionNames, true);
                })
                ->filter(fn (Module $module) => resolve_admin_route_name($module->route_name) !== null)
                ->map(function (Module $module) {
                    $menu = $this->menuFromModule($module);
                    $menu->route_name = resolve_admin_route_name($module->route_name);

                    return $menu;
                })
                ->values();

            return [
                'name' => $group->name,
                'sort_order' => $group->sort_order,
                'menus' => $menus,
            ];
        })->filter(fn (array $group) => $group['menus']->isNotEmpty())->values();

        return [
            'groups' => $navigationGroups,
            'ungrouped' => collect(),
        ];
    }

    /**
     * @return array{groups: Collection<int, array{name: string, sort_order: int, menus: Collection<int, Menu>}>, ungrouped: Collection<int, Menu>}
     */
    private function groupedFromMenusTable(?string $domain): array
    {
        $user = auth()->user();

        if (! $user || ! $domain) {
            return [
                'groups' => collect(),
                'ungrouped' => collect(),
            ];
        }

        $userPermissionNames = $user->getAllPermissions()->pluck('name')->toArray();

        $menus = Menu::query()
            ->where('domain', $domain)
            ->orderBy('sort_order')
            ->get()
            ->filter(function (Menu $menu) use ($userPermissionNames) {
                return $menu->permission_name === null || in_array($menu->permission_name, $userPermissionNames, true);
            });

        return [
            'groups' => collect(),
            'ungrouped' => $menus->values(),
        ];
    }

    private function menuFromModule(Module $module): Menu
    {
        return new Menu([
            'domain' => config('starterkit.admin_menu_domain', 'app'),
            'label' => $module->title,
            'route_name' => $module->route_name,
            'icon' => $module->icon,
            'sort_order' => $module->sort_order,
            'permission_name' => $module->sidebarPermissionName(),
        ]);
    }
}
