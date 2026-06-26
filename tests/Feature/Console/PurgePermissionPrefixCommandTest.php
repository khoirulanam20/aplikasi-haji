<?php

namespace Tests\Feature\Console;

use App\Support\Modules\PermissionGroupBuilder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurgePermissionPrefixCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_permission_prefix_removes_permissions_and_role_pivots(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'superadmin')->first();

        foreach (['list', 'create', 'update'] as $action) {
            $permission = Permission::firstOrCreate([
                'name' => "config_api.{$action}",
                'guard_name' => 'web',
            ]);
            $role->givePermissionTo($permission);
        }

        $this->assertDatabaseHas('permissions', ['name' => 'config_api.list']);

        $this->artisan('starterkit:purge-permission-prefix', [
            'prefix' => 'config_api',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'config_api.list']);
        $this->assertDatabaseMissing('permissions', ['name' => 'config_api.create']);

        $remaining = $role->fresh()->permissions->pluck('name');
        $this->assertFalse($remaining->contains('config_api.list'));
        $this->assertFalse($remaining->contains('config_api.create'));
    }

    public function test_purge_permission_prefix_hides_orphans_from_system_group(): void
    {
        Permission::firstOrCreate(['name' => 'config_api.list', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'config_api.update', 'guard_name' => 'web']);

        $this->artisan('starterkit:purge-permission-prefix', [
            'prefix' => 'config_api',
            '--force' => true,
        ])->assertSuccessful();

        $groups = app(PermissionGroupBuilder::class)->build();
        $systemGroup = $groups->firstWhere('code', 'system');

        if ($systemGroup !== null) {
            $codes = collect($systemGroup['modules'])->pluck('code');
            $this->assertFalse($codes->contains('config_api'));
        } else {
            $this->assertNull($systemGroup);
        }
    }

    public function test_purge_permission_prefix_rejects_invalid_prefix(): void
    {
        $this->artisan('starterkit:purge-permission-prefix', [
            'prefix' => 'bad.prefix',
            '--force' => true,
        ])->assertFailed();
    }
}
