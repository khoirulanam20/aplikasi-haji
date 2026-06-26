<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleRegistryService;
use Illuminate\Console\Command;

class PurgePermissionPrefixCommand extends Command
{
    protected $signature = 'starterkit:purge-permission-prefix
                            {prefix : Awalan permission, mis. config_api}
                            {--force : Hapus tanpa konfirmasi}';

    protected $description = 'Hapus permission orphan berdasarkan awalan (tanpa perlu record modul)';

    public function handle(ModuleRegistryService $registry): int
    {
        $prefix = trim((string) $this->argument('prefix'));

        if ($prefix === '' || ! preg_match('/^[a-z0-9_-]+$/i', $prefix)) {
            $this->error('Prefix tidak valid. Gunakan huruf, angka, underscore, atau strip saja.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Hapus semua permission \"{$prefix}.*\"?", true)) {
            $this->info('Dibatalkan.');

            return self::SUCCESS;
        }

        $deleted = $registry->removePermissionsByPrefix($prefix);

        if ($deleted === 0) {
            $this->warn("Tidak ada permission dengan awalan \"{$prefix}\".");

        } else {
            $this->info("Berhasil menghapus {$deleted} permission ({$prefix}.*).");
        }

        return self::SUCCESS;
    }
}
