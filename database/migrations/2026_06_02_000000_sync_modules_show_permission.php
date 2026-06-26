<?php

use App\Models\Module;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::query()->where('code', 'modules')->first();

        if (! $module) {
            return;
        }

        $enabled = $module->actions()
            ->where('is_enabled', true)
            ->pluck('action')
            ->push('show')
            ->unique()
            ->values()
            ->all();

        app(ModuleRegistryService::class)->syncActions($module, $enabled);
    }

    public function down(): void
    {
        // Permission modules.show tetap ada jika sudah dipakai; tidak dihapus otomatis.
    }
};
