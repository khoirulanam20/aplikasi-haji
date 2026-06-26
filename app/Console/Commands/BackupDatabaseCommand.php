<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database {--path= : Lokasi file backup}';

    protected $description = 'Backup database ke storage/app/backups (SQLite copy atau instruksi MySQL)';

    public function handle(): int
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Y-m-d_His');
        $targetPath = $this->option('path') ?: "{$backupDir}/{$connection}_{$timestamp}";

        if ($driver === 'sqlite') {
            $source = config("database.connections.{$connection}.database");

            if (! is_string($source) || ! file_exists($source)) {
                $this->error('File SQLite tidak ditemukan.');

                return self::FAILURE;
            }

            $file = "{$targetPath}.sqlite";
            File::copy($source, $file);
            $this->info("Backup SQLite: {$file}");

            return self::SUCCESS;
        }

        if ($driver === 'mysql') {
            $database = config("database.connections.{$connection}.database");
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");
            $file = "{$targetPath}.sql";

            $passwordArg = $password !== '' && $password !== null
                ? '-p'.escapeshellarg((string) $password)
                : '';

            $command = sprintf(
                'mysqldump -h %s -P %s -u %s %s %s > %s',
                escapeshellarg((string) $host),
                escapeshellarg((string) $port),
                escapeshellarg((string) $username),
                $passwordArg,
                escapeshellarg((string) $database),
                escapeshellarg($file),
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->error('mysqldump gagal. Pastikan mysqldump terpasang dan kredensial benar.');

                return self::FAILURE;
            }

            $this->info("Backup MySQL: {$file}");

            return self::SUCCESS;
        }

        $this->warn("Driver {$driver} belum didukung otomatis. Gunakan backup native DB Anda.");

        return self::FAILURE;
    }
}
