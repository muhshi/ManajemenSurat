<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $fillable = [
        'nama_pengirim',
        'jabatan_pengirim',
        'instansi_pengirim',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_diterima',
        'perihal',
        'isi_ringkas',
        'file_surat',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_diterima' => 'date',
    ];

    public function disposisis()
    {
        return $this->hasMany(Disposisi::class);
    }
}
