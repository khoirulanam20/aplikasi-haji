<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png', 200, 200),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        $this->assertStringEndsWith('.webp', $user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertNotNull($user->avatarUrl());
    }

    public function test_profile_avatar_rejects_invalid_file_type(): void
    {
        Storage::fake('public');

        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->userWithProfileAccess();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
