<?php

use App\Models\Module;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::query()->where('code', 'masterdata')->first();

        if (! $module) {
            return;
        }

        $module->update(['has_notifications' => true]);

        $enabled = $module->actions()
            ->where('is_enabled', true)
            ->pluck('action')
            ->push('notify')
            ->unique()
            ->values()
            ->all();

        app(ModuleRegistryService::class)->syncActions($module, $enabled, []);
    }

    public function down(): void
    {
        // Tidak mengembalikan flag/permission notify masterdata otomatis.
    }
};
