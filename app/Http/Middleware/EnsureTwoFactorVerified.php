<?php

namespace App\Http\Middleware;

use App\Support\Auth\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $this->twoFactor->isEnabledFor($user) && ! $request->session()->get('two_factor_passed')) {
            if (! $request->routeIs('two-factor.*', 'logout')) {
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
