<?php

use App\Models\Module;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $registry = app(ModuleRegistryService::class);

        Module::query()->each(function (Module $module) use ($registry): void {
            $enabled = $module->actions()
                ->where('is_enabled', true)
                ->pluck('action')
                ->reject(fn (string $action) => $action === 'tambah')
                ->values()
                ->all();

            $registry->syncActions($module, $enabled);
        });
    }

    public function down(): void
    {
        // Tidak mengembalikan centang tambah otomatis.
    }
};
