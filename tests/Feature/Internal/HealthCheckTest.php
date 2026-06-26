<?php

namespace Tests\Feature\Internal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_health_check_returns_component_status(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->withBasicAuth($user->email, 'password')
            ->getJson('/api/internal/health')
            ->assertOk()
            ->assertJsonStructure(['status', 'checks' => ['database', 'cache', 'queue']]);
    }

    public function test_internal_health_check_requires_basic_auth(): void
    {
        $this->getJson('/api/internal/health')->assertUnauthorized();
    }
}
