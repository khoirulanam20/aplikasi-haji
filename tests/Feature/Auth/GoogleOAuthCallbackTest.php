<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleOAuthCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);
    }

    private function mockGoogleUser(string $id, string $email, string $name = 'Google User'): void
    {
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn($id);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn($name);
        $googleUser->shouldReceive('getNickname')->andReturn('google-user');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_oauth_callback_blocks_inactive_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $this->mockGoogleUser('google-inactive', $user->email);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_oauth_callback_blocks_new_user_when_registration_disabled(): void
    {
        config(['starterkit.registration_enabled' => false]);

        $this->mockGoogleUser('google-new', 'newuser@example.com');

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    public function test_oauth_callback_logs_in_active_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'is_active' => true,
        ]);

        $this->mockGoogleUser('google-active', $user->email);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-active', $user->fresh()->google_id);
    }
}
