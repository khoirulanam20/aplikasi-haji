<?php

namespace App\Support\Modules;

use App\Models\Module;
use App\Models\User;
use App\Notifications\CrudActivityNotification;

class ModuleNotification
{
    public static function sendIfAllowed(User $user, string $moduleCode, string $title, string $message): void
    {
        $module = Module::query()->where('code', $moduleCode)->first();

        if (! $module?->has_notifications || ! $user->can("{$moduleCode}.notify")) {
            return;
        }

        $user->notify(new CrudActivityNotification($title, $message));
    }
}
