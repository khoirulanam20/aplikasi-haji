<?php

namespace App\Support\Hajj;

use App\Models\HajjParticipant;
use App\Models\Role;
use App\Models\User;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class HajjParticipantUserProvisioner
{
    /**
     * @return array{user: User, password: string}
     */
    public function provision(HajjParticipant $participant, ?string $email = null): array
    {
        app(IntegrationConfigService::class)->apply();

        $roleName = (string) config('starterkit.hajj_participant_role', 'user');

        if (! Role::query()->where('name', $roleName)->where('is_active', true)->exists()) {
            throw new RuntimeException("Role \"{$roleName}\" belum dibuat. Buat role tersebut di menu Role & Permission terlebih dahulu.");
        }

        $username = $this->resolveUsername($participant);
        $domain = (string) config('starterkit.hajj_participant_email_domain', 'peserta-haji.local');
        $email = $email ?: $username.'@'.$domain;
        $password = Str::password(12);

        $user = User::create([
            'name' => $participant->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $user->syncRoles([$roleName]);

        $participant->update(['user_id' => $user->id]);

        return ['user' => $user, 'password' => $password];
    }

    private function resolveUsername(HajjParticipant $participant): string
    {
        if ($participant->nomor_porsi) {
            $base = Str::slug($participant->nomor_porsi, '');
            if ($base !== '' && ! User::query()->where('username', $base)->exists()) {
                return $base;
            }
        }

        $base = Str::slug($participant->nama, '_');
        if ($base === '') {
            $base = 'peserta';
        }

        $username = $base;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.'_'.$suffix;
            $suffix++;
        }

        return $username;
    }
}
