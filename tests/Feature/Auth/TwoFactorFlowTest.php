<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TwoFactorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['starterkit.two_factor_enabled' => true]);

        foreach (['profile.read', 'profile.update'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function userWithProfileAccess(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['profile.read', 'profile.update']);

        return $user;
    }

    public function test_user_can_enable_two_factor_and_complete_challenge_after_login(): void
    {
        $user = $this->userWithProfileAccess();
        $google2fa = app(Google2FA::class);

        $this->actingAs($user)
            ->post('/profile/two-factor/enable')
            ->assertRedirect();

        $secret = session('two_factor_setup_secret');
        $this->assertIsString($secret);

        $code = $google2fa->getCurrentOtp($secret);

        $this->actingAs($user)
            ->post('/profile/two-factor/confirm', ['code' => $code])
            ->assertRedirect()
            ->assertSessionHas('status', 'two-factor-enabled');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $this->get('/dashboard')->assertRedirect(route('two-factor.challenge'));

        $challengeCode = $google2fa->getCurrentOtp($secret);

        $this->post('/two-factor/challenge', ['code' => $challengeCode])
            ->assertRedirect(route('dashboard'));
    }

    public function test_two_factor_routes_return_404_when_feature_disabled(): void
    {
        config(['starterkit.two_factor_enabled' => false]);

        $user = $this->userWithProfileAccess();

        $this->actingAs($user)
            ->post('/profile/two-factor/enable')
            ->assertNotFound();
    }
}
