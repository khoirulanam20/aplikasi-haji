<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ImpersonateController extends Controller
{
    public function start(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->can('impersonate.start'), 403);
        abort_if(auth()->id() === $user->id, 422, 'Tidak bisa impersonate akun sendiri.');
        abort_unless($user->is_active, 422, 'Tidak bisa impersonate pengguna nonaktif.');
        abort_if($user->hasRole('superadmin'), 422, 'Tidak bisa impersonate akun superadmin.');

        session([
            'impersonator_id' => auth()->id(),
            'impersonation_log_id' => ImpersonationLog::create([
                'impersonator_id' => auth()->id(),
                'impersonated_id' => $user->id,
                'started_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        Auth::login($user);

        Cache::forget("menu.{$user->id}");

        return redirect()->route('dashboard')->with('status', 'impersonation-started');
    }

    public function stop(): RedirectResponse
    {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403);

        if (session()->has('impersonation_log_id')) {
            ImpersonationLog::whereKey(session('impersonation_log_id'))->update(['ended_at' => now()]);
        }

        Auth::loginUsingId($impersonatorId);
        session()->forget(['impersonator_id', 'impersonation_log_id', 'impersonation_started_at']);

        Cache::forget("menu.{$impersonatorId}");

        return redirect()->route('dashboard')->with('status', 'impersonation-stopped');
    }
}
