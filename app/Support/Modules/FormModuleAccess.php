<?php

namespace App\Support\Modules;

use App\Models\User;

class FormModuleAccess
{
    public function canView(string $code, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can("{$code}.list") || $user->can("{$code}.read");
    }

    public function canSave(string $code, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can("{$code}.create") && $user->can("{$code}.update");
    }
}
