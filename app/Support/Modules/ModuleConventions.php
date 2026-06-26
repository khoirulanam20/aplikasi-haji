<?php

namespace App\Support\Modules;

use InvalidArgumentException;

class ModuleConventions
{
    /**
     * @return array<string, string> deprecated code => message
     */
    public static function deprecatedCodes(): array
    {
        return config('starterkit.module_conventions.deprecated_codes', []);
    }

    /**
     * @return array<string, string> route_name => required module code
     */
    public static function routes(): array
    {
        return config('starterkit.module_conventions.routes', []);
    }

    public static function requiredCodeForRoute(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        $resolved = resolve_admin_route_name($routeName) ?? $routeName;

        return self::routes()[$resolved] ?? self::routes()[$routeName] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function routesForCode(string $code): array
    {
        return array_keys(array_filter(
            self::routes(),
            fn (string $requiredCode) => $requiredCode === $code
        ));
    }

    public static function assertConfigIsValid(): void
    {
        $deprecated = array_keys(self::deprecatedCodes());
        $canonicalCodes = array_values(self::routes());

        foreach (self::routes() as $route => $code) {
            if (in_array($code, $deprecated, true)) {
                throw new InvalidArgumentException(
                    "Konfigurasi module_conventions.routes[{$route}] memakai kode deprecated [{$code}]."
                );
            }
        }

        foreach ($canonicalCodes as $code) {
            if (in_array($code, $deprecated, true)) {
                throw new InvalidArgumentException(
                    "Kode canonical [{$code}] tidak boleh ada di deprecated_codes."
                );
            }
        }
    }
}
