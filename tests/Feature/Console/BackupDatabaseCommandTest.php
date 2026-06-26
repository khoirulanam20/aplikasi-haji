<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupDatabaseCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_backup_creates_file(): void
    {
        $source = storage_path('framework/testing/backup-source.sqlite');
        $backupBase = storage_path('framework/testing/backups/regression_backup');

        File::deleteDirectory(dirname($backupBase));
        File::ensureDirectoryExists(dirname($backupBase));
        File::put($source, '');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $source,
        ]);

        $this->artisan('backup:database', ['--path' => $backupBase])
            ->assertSuccessful();

        $this->assertFileExists("{$backupBase}.sqlite");

        File::deleteDirectory(storage_path('framework/testing/backups'));
        File::delete($source);
    }
}
