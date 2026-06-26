<?php

namespace App\Http\Middleware;

use App\Support\Modules\ModulePermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    public function __construct(private readonly ModulePermissionResolver $resolver)
    {
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $routeSuffix, string $action): Response
    {
        $routeName = admin_route_name($routeSuffix);
        $user = $request->user();

        foreach ($this->resolver->aliasesForRoute($routeName, $action) as $permission) {
            if ($user?->can($permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
