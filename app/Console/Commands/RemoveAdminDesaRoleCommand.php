<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class RemoveAdminDesaRoleCommand extends Command
{
    protected $signature = 'starterkit:remove-admin-desa-role';

    protected $description = 'Hapus role admin_desa, menu domain admin_desa, dan detach dari user';

    public function handle(): int
    {
        Menu::query()->where('domain', 'admin_desa')->delete();

        $role = Role::query()->where('name', 'admin_desa')->where('guard_name', 'web')->first();

        if ($role) {
            $role->users()->detach();
            $role->permissions()->detach();
            $role->delete();
            $this->info('Role admin_desa dihapus.');
        } else {
            $this->info('Role admin_desa tidak ditemukan (sudah dihapus).');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return self::SUCCESS;
    }
}
