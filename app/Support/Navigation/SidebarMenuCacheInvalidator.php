<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Cache;

class SidebarMenuCacheInvalidator
{
    public function invalidate(): void
    {
        Cache::forever('menu_sidebars', null);
    }
}
