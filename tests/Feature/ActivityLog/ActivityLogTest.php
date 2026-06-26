<?php

namespace Tests\Feature\ActivityLog;

use App\Models\MasterData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions): User
    {
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_master_data_changes_are_logged(): void
    {
        $user = $this->userWithPermissions(['masterdata.list', 'masterdata.create']);

        $this->actingAs($user)->post('/app/master-data', [
            'name' => 'Item Test',
            'description' => 'Deskripsi',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'created',
            'event' => 'created',
            'subject_type' => MasterData::class,
        ]);
    }

    public function test_activity_log_index_requires_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/activity-log')
            ->assertForbidden();
    }

    public function test_activity_log_index_lists_activities(): void
    {
        $user = $this->userWithPermissions(['activitylog.list']);

        activity()
            ->causedBy($user)
            ->log('Test aktivitas manual');

        $response = $this->actingAs($user)->get('/app/activity-log');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/ActivityLog/Index')
            ->has('activities.data')
            ->where('activities.data', fn ($data) => collect($data)->contains(
                fn ($item) => ($item['description'] ?? null) === 'Test aktivitas manual'
            ))
        );
    }

    public function test_activity_log_can_be_deleted_with_permission(): void
    {
        $user = $this->userWithPermissions(['activitylog.list', 'activitylog.delete']);

        $activity = activity()
            ->causedBy($user)
            ->log('Log untuk dihapus');

        $this->actingAs($user)
            ->delete('/app/activity-log/'.$activity->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('activity_log', [
            'id' => $activity->id,
        ]);
    }

    public function test_sensitive_user_fields_are_not_logged(): void
    {
        $user = $this->userWithPermissions(['users.list', 'users.create']);

        $this->actingAs($user)->post('/app/users', [
            'name' => 'User Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_ids' => [],
        ])->assertRedirect();

        $activity = Activity::query()->where('subject_type', User::class)->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertArrayNotHasKey('password', $activity->properties->get('attributes', []));
    }
}
