<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2dUpload extends Model
{
    protected $fillable = [
        'file_monitoring_sp2d',
        'file_potongan_spm',
        'periode_bulan',
        'periode_tahun',
        'total_sp2d_terproses',
        'status',
        'error_log',
        'user_id',
    ];

    public function rekaps(): HasMany
    {
        return $this->hasMany(Sp2dRekap::class, 'upload_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
