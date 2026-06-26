<?php

namespace App\Support\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    public function enabled(): bool
    {
        return (bool) config('starterkit.two_factor_enabled');
    }

    public function isEnabledFor(User $user): bool
    {
        return $this->enabled() && $user->two_factor_confirmed_at !== null;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function verify(User $user, string $code): bool
    {
        $secret = $this->decryptSecret($user);

        if ($secret === null) {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code);
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(User $user): ?string
    {
        if ($user->two_factor_secret === null) {
            return null;
        }

        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable) {
            return null;
        }
    }

    public function provisioningUri(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );
    }
}
