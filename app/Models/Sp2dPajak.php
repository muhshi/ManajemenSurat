<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sp2dPajak extends Model
{
    protected $fillable = [
        'rekap_id',
        'jenis_pajak',
        'jumlah_pajak',
    ];

    protected $casts = [
        'jumlah_pajak' => 'integer',
    ];

    public function rekap(): BelongsTo
    {
        return $this->belongsTo(Sp2dRekap::class, 'rekap_id');
    }
}
