<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2dRekap extends Model
{
    protected $fillable = [
        'upload_id',
        'no_spp',
        'uraian_spp',
        'jenis_spp',
        'tanggal_spp',
        'jumlah_pengeluaran',
        'jumlah_potongan',
        'jumlah_pembayaran',
        'tanggal_sp2d',
        'no_sp2d',
        'status_sp2d',
        'kppn',
        'nama_satker',
        'periode',
    ];

    protected $casts = [
        'tanggal_spp' => 'date',
        'tanggal_sp2d' => 'date',
        'jumlah_pengeluaran' => 'integer',
        'jumlah_potongan' => 'integer',
        'jumlah_pembayaran' => 'integer',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Sp2dUpload::class, 'upload_id');
    }

    public function pajaks(): HasMany
    {
        return $this->hasMany(Sp2dPajak::class, 'rekap_id');
    }
}
