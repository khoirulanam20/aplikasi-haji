<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    protected function baseActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return $this->baseActivitylogOptions();
    }
}
