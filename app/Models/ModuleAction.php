<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleAction extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'module_id',
        'action',
        'label',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
