<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAdminRouteNamesCommand extends Command
{
    protected $signature = 'starterkit:migrate-route-names {--from=superadmin} {--to=app}';

    protected $description = 'Migrasi route_name modul dan domain menu dari prefix lama ke prefix baru';

    public function handle(): int
    {
        $from = (string) $this->option('from');
        $to = (string) $this->option('to');
        $fromPrefix = $from.'.';
        $toPrefix = $to.'.';

        $modulesUpdated = 0;
        Module::query()
            ->whereNotNull('route_name')
            ->where('route_name', 'like', $fromPrefix.'%')
            ->orderBy('id')
            ->each(function (Module $module) use ($fromPrefix, $toPrefix, &$modulesUpdated): void {
                $module->update([
                    'route_name' => $toPrefix.substr((string) $module->route_name, strlen($fromPrefix)),
                ]);
                $modulesUpdated++;
            });

        $menusDomainUpdated = Menu::query()
            ->where('domain', $from)
            ->update(['domain' => $to]);

        $menusRouteUpdated = 0;
        Menu::query()
            ->whereNotNull('route_name')
            ->where('route_name', 'like', $fromPrefix.'%')
            ->orderBy('id')
            ->each(function (Menu $menu) use ($fromPrefix, $toPrefix, &$menusRouteUpdated): void {
                $menu->update([
                    'route_name' => $toPrefix.substr((string) $menu->route_name, strlen($fromPrefix)),
                ]);
                $menusRouteUpdated++;
            });

        DB::table('menus')
            ->where('domain', $from)
            ->whereNull('route_name')
            ->update(['domain' => $to]);

        $this->info("Modules route_name updated: {$modulesUpdated}");
        $this->info("Menus domain updated: {$menusDomainUpdated}");
        $this->info("Menus route_name updated: {$menusRouteUpdated}");

        return self::SUCCESS;
    }
}
