<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('SUPERADMIN_PASSWORD', 'password');

        if (app()->environment('production') && $password === 'password') {
            throw new \RuntimeException(
                'SUPERADMIN_PASSWORD wajib diset di environment production. Jangan gunakan password default.'
            );
        }

        $user = User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@example.com')],
            [
                'name' => 'Superadmin',
                'username' => 'superadmin',
                'password' => Hash::make($password),
            ]
        );

        $user->syncRoles(['superadmin']);
    }
}
