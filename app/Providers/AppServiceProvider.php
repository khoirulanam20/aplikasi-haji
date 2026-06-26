<?php

namespace App\Providers;

use App\Support\Modules\ModuleConventions;
use App\Support\Settings\IntegrationConfigService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ModuleConventions::assertConfigIsValid();

        app(IntegrationConfigService::class)->apply();
    }
}
