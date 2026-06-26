<?php

namespace App\Jobs;

use App\Support\Hajj\HajjExcelImportService;
use App\Support\Hajj\HajjImportProgress;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportHajjParticipantsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(
        private readonly string $storedPath,
        private readonly int $tahunHaji,
        private readonly ?int $createdBy,
        private readonly string $token,
        private readonly string $duplicateAction = 'skip',
    ) {}

    public function handle(HajjExcelImportService $importService): void
    {
        $absolutePath = Storage::path($this->storedPath);

        try {
            $summary = $importService->import(
                $absolutePath,
                $this->tahunHaji,
                $this->createdBy,
                $this->duplicateAction,
                function (int $processed, int $total): void {
                    HajjImportProgress::processing($this->token, $processed, $total);
                },
            );

            HajjImportProgress::completed($this->token, $summary);
        } catch (Throwable $e) {
            HajjImportProgress::failed($this->token, $e->getMessage());
            throw $e;
        } finally {
            Storage::delete($this->storedPath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            HajjImportProgress::failed($this->token, $exception->getMessage());
        }

        Storage::delete($this->storedPath);
    }
}
