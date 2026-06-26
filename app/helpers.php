<?php

if (! function_exists('admin_route_name')) {
    function admin_route_name(string $name): string
    {
        return config('starterkit.admin_route_name').'.'.$name;
    }
}

if (! function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        $prefix = trim(config('starterkit.admin_prefix'), '/');
        $path = ltrim($path, '/');

        return $path === '' ? '/'.$prefix : '/'.$prefix.'/'.$path;
    }
}

if (! function_exists('resolve_admin_route_name')) {
    function resolve_admin_route_name(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            return $routeName;
        }

        if (str_starts_with($routeName, 'superadmin.')) {
            $migrated = admin_route_name(substr($routeName, strlen('superadmin.')));

            if (\Illuminate\Support\Facades\Route::has($migrated)) {
                return $migrated;
            }
        }

        return null;
    }
}
