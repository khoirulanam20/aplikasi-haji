<?php

use App\Models\Module;
use App\Models\ModuleAction;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_actions', function (Blueprint $table) {
            $table->string('label', 100)->nullable()->after('action');
        });

        ModuleAction::query()->where('action', 'tambah')->delete();

        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', '%.tambah')
            ->delete();

        Module::query()->each(function (Module $module): void {
            $enabled = $module->actions()
                ->where('is_enabled', true)
                ->where('action', '!=', 'tambah')
                ->pluck('action')
                ->all();

            app(ModuleRegistryService::class)->syncActions($module, $enabled, []);
        });
    }

    public function down(): void
    {
        Schema::table('module_actions', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
