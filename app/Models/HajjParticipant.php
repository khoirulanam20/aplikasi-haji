<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HajjParticipant extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'tahun_haji',
        'nomor_porsi',
        'nama',
        'alamat',
        'desa',
        'kecamatan',
        'telepon',
        'kloter',
        'rombongan',
        'regu',
        'user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tahun_haji' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
