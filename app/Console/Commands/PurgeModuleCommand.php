<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class PurgeModuleCommand extends Command
{
    protected $signature = 'starterkit:purge-module
                            {code : Kode modul yang akan dihapus}
                            {--force : Hapus tanpa konfirmasi}';

    protected $description = 'Hapus modul beserta permission dan menu sidebar terkait';

    public function handle(ModuleRegistryService $registry): int
    {
        $code = $this->argument('code');

        $module = Module::query()->where('code', $code)->first();

        if (! $module) {
            $this->warn("Modul \"{$code}\" tidak ditemukan.");
            $this->line("Jika permission masih muncul di Role Permission, jalankan:");
            $this->line("  php artisan starterkit:purge-permission-prefix {$code} --force");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Hapus modul \"{$module->title}\" ({$code}) beserta permission-nya?", true)) {
            $this->info('Dibatalkan.');

            return self::SUCCESS;
        }

        $registry->removeModule($module);
        $module->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::forever('menu_sidebars', null);

        $this->info("Modul \"{$code}\" berhasil dihapus.");

        return self::SUCCESS;
    }
}
