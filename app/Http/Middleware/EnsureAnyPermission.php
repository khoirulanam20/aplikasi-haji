<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyPermission
{
    /**
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless(
            collect($permissions)->contains(fn (string $permission) => $user?->can($permission)),
            403
        );

        return $next($request);
    }
}
