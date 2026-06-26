<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Module;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MigratePermissionSuffixesCommand extends Command
{
    protected $signature = 'starterkit:migrate-permission-suffixes';

    protected $description = 'Migrasi permission *.view ke *.list dan re-sync permission modul (aksi 1:1)';

    public function handle(): int
    {
        $viewPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', '%.view')
            ->get();

        $migrated = 0;

        foreach ($viewPermissions as $viewPermission) {
            $listName = str_replace('.view', '.list', $viewPermission->name);

            if ($listName === $viewPermission->name) {
                continue;
            }

            $listPermission = Permission::firstOrCreate([
                'name' => $listName,
                'guard_name' => 'web',
            ]);

            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $viewPermission->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $listPermission->id,
                    'role_id' => $roleId,
                ]);
            }

            $userIds = DB::table('model_has_permissions')
                ->where('permission_id', $viewPermission->id)
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                DB::table('model_has_permissions')->insertOrIgnore([
                    'permission_id' => $listPermission->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId,
                ]);
            }

            Menu::query()
                ->where('permission_name', $viewPermission->name)
                ->update(['permission_name' => $listName]);

            DB::table('role_has_permissions')
                ->where('permission_id', $viewPermission->id)
                ->delete();

            DB::table('model_has_permissions')
                ->where('permission_id', $viewPermission->id)
                ->delete();

            $viewPermission->delete();
            $migrated++;
        }

        $profileView = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'profile.view')
            ->first();

        if ($profileView) {
            $profileRead = Permission::firstOrCreate([
                'name' => 'profile.read',
                'guard_name' => 'web',
            ]);

            $this->migratePermissionAssignments($profileView, $profileRead);

            Menu::query()
                ->where('permission_name', 'profile.view')
                ->update(['permission_name' => 'profile.read']);

            $profileView->delete();
            $migrated++;
        }

        $registry = app(ModuleRegistryService::class);

        Module::query()
            ->with('actions')
            ->orderBy('id')
            ->each(function (Module $module) use ($registry): void {
                $enabled = $module->actions
                    ->where('is_enabled', true)
                    ->pluck('action')
                    ->all();

                $registry->syncActions($module, $enabled);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Permission *.view migrated: {$migrated}");
        $this->info('All modules re-synced with 1:1 action permissions.');

        return self::SUCCESS;
    }

    private function migratePermissionAssignments(Permission $from, Permission $to): void
    {
        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $from->id)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $to->id,
                'role_id' => $roleId,
            ]);
        }

        DB::table('role_has_permissions')
            ->where('permission_id', $from->id)
            ->delete();
    }
}
