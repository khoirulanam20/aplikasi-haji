<?php

namespace App\Support\Auth;

use App\Models\Module;
use App\Models\User;

class UserNavigationDomain
{
    public function adminMenuDomain(): string
    {
        return (string) config('starterkit.admin_menu_domain', 'app');
    }

    public function resolve(User $user): ?string
    {
        if ($user->hasRole('superadmin') || $this->hasAdminPanelModulePermission($user)) {
            return $this->adminMenuDomain();
        }

        return null;
    }

    public function dashboardRouteNameFor(User $user): ?string
    {
        if ($this->resolve($user) === $this->adminMenuDomain() && $user->can('dashboard.list')) {
            return admin_route_name('dashboard');
        }

        if ($user->hajjParticipant()->exists() && $user->can('profile.read')) {
            return 'profile.edit';
        }

        return null;
    }

    private function hasAdminPanelModulePermission(User $user): bool
    {
        if ($user->roles()->doesntExist()) {
            return false;
        }

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return Module::query()
            ->with('enabledActions')
            ->where('is_active', true)
            ->get()
            ->contains(function (Module $module) use ($userPermissions) {
                foreach ($module->permissionNames() as $permission) {
                    if (in_array($permission, $userPermissions, true)) {
                        return true;
                    }
                }

                return false;
            });
    }
}
