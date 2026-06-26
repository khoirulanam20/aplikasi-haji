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
                ->all();

            if ($module->layout_type !== Module::LAYOUT_FORM_BASE) {
                $preset = Module::actionPreset(Module::LAYOUT_TABLE_BASE);
                $shouldEnableTambah = in_array('create', $enabled, true)
                    || in_array('create', $preset, true);

                if ($shouldEnableTambah && ! in_array('tambah', $enabled, true)) {
                    $enabled[] = 'tambah';
                }
            }

            $registry->syncActions($module, $enabled);
        });
    }

    public function down(): void
    {
        // Baris action tambah tetap; tidak di-rollback otomatis.
    }
};
