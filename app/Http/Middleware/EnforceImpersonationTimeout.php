<?php

namespace App\Http\Middleware;

use App\Models\ImpersonationLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceImpersonationTimeout
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeoutMinutes = config('starterkit.impersonation_timeout_minutes');

        if ($timeoutMinutes > 0 && $request->session()->has('impersonator_id')) {
            $startedAt = $request->session()->get('impersonation_started_at');

            if ($startedAt && now()->diffInMinutes(Carbon::parse($startedAt)) >= $timeoutMinutes) {
                $impersonatorId = $request->session()->get('impersonator_id');

                if ($request->session()->has('impersonation_log_id')) {
                    ImpersonationLog::whereKey($request->session()->get('impersonation_log_id'))
                        ->update(['ended_at' => now()]);
                }

                Auth::loginUsingId($impersonatorId);
                $request->session()->forget([
                    'impersonator_id',
                    'impersonation_log_id',
                    'impersonation_started_at',
                ]);

                return redirect()
                    ->route('dashboard')
                    ->with('status', 'impersonation-timeout');
            }
        }

        return $next($request);
    }
}
