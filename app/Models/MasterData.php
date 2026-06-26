<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'description',
    ];
}
