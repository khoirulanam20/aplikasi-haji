<?php

namespace Tests\Feature\Auth;

use App\Support\Settings\IntegrationConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleOAuthConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_is_blocked_when_oauth_not_configured(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
    }

    public function test_google_redirect_is_allowed_when_oauth_configured(): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'client-secret',
        ]);

        $this->assertTrue(app(IntegrationConfigService::class)->googleOAuthEnabled());

        $this->get('/auth/google/redirect')->assertRedirect();
    }
}
