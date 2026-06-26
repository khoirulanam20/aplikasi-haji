<?php

namespace App\Support\Auth;

use App\Models\User;

class ProfileAccess
{
    public function canView(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('profile.read') || $user->can('profile.list');
    }

    public function canUpdate(?User $user): bool
    {
        return (bool) $user?->can('profile.update');
    }
}
