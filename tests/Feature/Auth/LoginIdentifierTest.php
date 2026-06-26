<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginIdentifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username(): void
    {
        User::create([
            'name' => 'Tester',
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'login' => 'tester',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
