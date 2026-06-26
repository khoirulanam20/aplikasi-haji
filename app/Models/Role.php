<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'title',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        return $this->title ?: $this->name;
    }
}
