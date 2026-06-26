<?php

namespace App\Http\Controllers;

use App\Support\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorSettingsController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function show(Request $request): Response
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('two_factor_setup_secret');

        return Inertia::render('Profile/TwoFactor', [
            'enabled' => $this->twoFactor->isEnabledFor($user),
            'featureEnabled' => $this->twoFactor->enabled(),
            'provisioningUri' => $pendingSecret
                ? $this->twoFactor->provisioningUri($user, $pendingSecret)
                : null,
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        abort_unless($this->twoFactor->enabled(), 404);

        $secret = $this->twoFactor->generateSecret();
        $request->session()->put('two_factor_setup_secret', $secret);

        return back();
    }

    public function confirm(Request $request): RedirectResponse
    {
        abort_unless($this->twoFactor->enabled(), 404);

        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $secret = $request->session()->get('two_factor_setup_secret');
        abort_unless(is_string($secret), 422);

        $user = $request->user();
        $user->two_factor_secret = $this->twoFactor->encryptSecret($secret);
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->put('two_factor_passed', true);

        return back()->with('status', 'two-factor-enabled');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget(['two_factor_setup_secret', 'two_factor_passed']);

        return back()->with('status', 'two-factor-disabled');
    }
}
