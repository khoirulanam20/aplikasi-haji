<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $list = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'profile.list')
            ->first();

        if (! $list) {
            return;
        }

        $read = Permission::firstOrCreate([
            'name' => 'profile.read',
            'guard_name' => 'web',
        ]);

        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $list->id)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $read->id,
                'role_id' => $roleId,
            ]);
        }

        DB::table('role_has_permissions')->where('permission_id', $list->id)->delete();

        $modelPivots = DB::table('model_has_permissions')
            ->where('permission_id', $list->id)
            ->get();

        foreach ($modelPivots as $pivot) {
            DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $read->id,
                'model_type' => $pivot->model_type,
                'model_id' => $pivot->model_id,
            ]);
        }

        DB::table('model_has_permissions')->where('permission_id', $list->id)->delete();

        $list->delete();
    }

    public function down(): void
    {
        // Tidak mengembalikan profile.list — gunakan profile.read saja.
    }
};
