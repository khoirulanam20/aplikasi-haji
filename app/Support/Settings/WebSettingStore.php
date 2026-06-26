<?php

namespace App\Support\Settings;

use App\Models\WebSetting;

class WebSettingStore
{
    public function first(): ?WebSetting
    {
        return WebSetting::query()->first();
    }

    public function appName(): ?string
    {
        return $this->first()?->app_name;
    }
}
