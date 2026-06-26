<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'impersonator_id',
        'impersonated_id',
        'started_at',
        'ended_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
