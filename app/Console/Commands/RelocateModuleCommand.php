<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RelocateModuleCommand extends Command
{
    protected $signature = 'starterkit:relocate-module
                            {module : Kode modul, mis. config_api}
                            {group : Kode grup modul, mis. pengaturan_website}';

    protected $description = 'Pindahkan modul ke grup modul yang ditentukan';

    public function handle(): int
    {
        $moduleCode = $this->argument('module');
        $groupCode = $this->argument('group');

        $module = Module::query()->where('code', $moduleCode)->first();

        if (! $module) {
            $this->error("Modul \"{$moduleCode}\" tidak ditemukan.");

            return self::FAILURE;
        }

        $group = ModuleGroup::query()->where('code', $groupCode)->first();

        if (! $group) {
            $this->error("Grup modul \"{$groupCode}\" tidak ditemukan.");

            return self::FAILURE;
        }

        if ($module->module_group_id === $group->id) {
            $this->info("Modul \"{$moduleCode}\" sudah berada di grup \"{$group->name}\" ({$groupCode}).");

            return self::SUCCESS;
        }

        $module->update(['module_group_id' => $group->id]);

        Cache::forever('menu_sidebars', null);

        $this->info("Modul \"{$moduleCode}\" dipindahkan ke grup \"{$group->name}\" ({$groupCode}).");

        return self::SUCCESS;
    }
}
