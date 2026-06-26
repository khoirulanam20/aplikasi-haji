<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! app(IntegrationConfigService::class)->googleOAuthEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Login Google belum dikonfigurasi.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! app(IntegrationConfigService::class)->googleOAuthEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Login Google belum dikonfigurasi.']);
        }

        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())
            ->orWhere('google_id', $googleUser->getId())
            ->first();

        if (! $user) {
            if (! config('starterkit.registration_enabled')) {
                return redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Akun tidak ditemukan. Registrasi publik dinonaktifkan.']);
            }

            $user = User::create([
                'name' => $googleUser->getName() ?: 'Google User',
                'username' => Str::slug($googleUser->getNickname() ?: $googleUser->getName() ?: 'google-user').'-'.Str::lower(Str::random(4)),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Str::password(32),
                'is_active' => true,
            ]);
        } else {
            if (! $user->is_active) {
                return redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Akun Anda tidak aktif. Hubungi administrator.']);
            }

            $user->google_id = $googleUser->getId();
            $user->save();
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
