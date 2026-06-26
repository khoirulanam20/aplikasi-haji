<?php

namespace App\Support\Modules;

use Illuminate\Validation\ValidationException;

class ModuleConventionValidator
{
    /**
     * @param  array{code: string, route_name?: string|null}  $payload
     */
    public function validate(array $payload): void
    {
        $errors = [];
        $code = $payload['code'] ?? '';
        $routeName = $payload['route_name'] ?? null;

        if ($code !== '' && isset(ModuleConventions::deprecatedCodes()[$code])) {
            $errors['code'] = ModuleConventions::deprecatedCodes()[$code];
        }

        $requiredForRoute = ModuleConventions::requiredCodeForRoute($routeName);

        if ($requiredForRoute !== null && $code !== $requiredForRoute) {
            $errors['code'] = "Route \"{$routeName}\" wajib memakai kode modul \"{$requiredForRoute}\".";
        }

        $allowedRoutes = ModuleConventions::routesForCode($code);

        if ($allowedRoutes !== [] && ($routeName === null || $routeName === '')) {
            $errors['route_name'] = 'Route name wajib diisi untuk modul ini (contoh: '.implode(', ', $allowedRoutes).').';
        } elseif ($allowedRoutes !== [] && $routeName !== null && $routeName !== '') {
            $resolved = resolve_admin_route_name($routeName) ?? $routeName;

            if (! in_array($resolved, $allowedRoutes, true) && ! in_array($routeName, $allowedRoutes, true)) {
                $errors['route_name'] = "Kode \"{$code}\" hanya boleh memakai route: ".implode(', ', $allowedRoutes).'.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
