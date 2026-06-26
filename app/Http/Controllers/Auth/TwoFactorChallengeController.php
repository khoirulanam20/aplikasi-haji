<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function create(): Response|RedirectResponse
    {
        $user = auth()->user();

        if (! $user || ! $this->twoFactor->isEnabledFor($user)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = $request->user();

        if (! $user || ! $this->twoFactor->verify($user, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
        }

        $request->session()->put('two_factor_passed', true);

        return redirect()->intended(route('dashboard'));
    }
}
