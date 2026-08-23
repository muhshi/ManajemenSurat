<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2dRekap extends Model
{
    protected $fillable = [
        'upload_id',
        'no_sp2d',
        'tgl_sp2d',
        'no_spm',
        'tgl_spm',
        'jenis_spm',
        'jalur_transaksi',
        'uraian',
        'jumlah_pengeluaran',
        'jumlah_potongan',
        'jumlah_pembayaran',
        'atas_nama_default',
        'status_verifikasi',
    ];

    protected $casts = [
        'tgl_spm' => 'date',
        'tgl_sp2d' => 'date',
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
        return $this->hasMany(Sp2dPajak::class, 'sp2d_rekap_id');
    }

    public function getTotalPajakAttribute(): int
    {
        return $this->pajaks->sum('nominal_pajak');
    }

    public function getSelisihPotonganAttribute(): int
    {
        return $this->jumlah_potongan - $this->total_pajak;
    }

    public function isBalanced(): bool
    {
        return $this->selisih_potongan === 0;
    }
}
