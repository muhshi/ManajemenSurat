<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sp2dPajak extends Model
{
    protected $fillable = [
        'sp2d_rekap_id',
        'npwp_nik',
        'nama_pihak',
        'kode_akun_pajak',
        'nama_akun_pajak',
        'dpp',
        'nominal_pajak',
        'no_drpp_kuitansi',
        'ntpn_billing',
    ];

    protected $casts = [
        'dpp' => 'integer',
        'nominal_pajak' => 'integer',
    ];

    public function rekap(): BelongsTo
    {
        return $this->belongsTo(Sp2dRekap::class, 'sp2d_rekap_id');
    }
}
