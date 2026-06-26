<?php

namespace App\Support\Auth;

use App\Models\User;

class DashboardRedirect
{
    public function __construct(private readonly UserNavigationDomain $navigationDomain)
    {
    }

    public function routeNameFor(User $user): ?string
    {
        return $this->navigationDomain->dashboardRouteNameFor($user);
    }
}
