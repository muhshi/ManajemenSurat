<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bmn extends Model
{
    protected $table = 'bmns';

    protected $fillable = [
        'kode_barang',
        'nup',
        'nama_barang',
        'jenis_bmn',
        'merk',
        'tipe',
        'kondisi',
        'umur_aset',
        'henti_guna',
        'nilai_perolehan',
        'nilai_buku',
        'nilai_penyusutan',
        'tanggal_perolehan',
        'no_polisi',
        'no_dokumen',
        'status_penggunaan',
        'intra_extra',
        'usul_hapus',
        'alamat',
        'kode_register',
        'ruangan_id',
        'penanggung_jawab_type',
        'penanggung_jawab_id',
        'catatan',
        'foto',
    ];

    protected $casts = [
        'henti_guna' => 'boolean',
        'usul_hapus' => 'boolean',
        'nilai_perolehan' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'nilai_penyusutan' => 'decimal:2',
        'tanggal_perolehan' => 'date',
        'foto' => 'array',
        'umur_aset' => 'integer',
        'nup' => 'integer',
    ];

    // Lokasi ruangan aset ini berada
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    // Polymorphic: penanggung jawab bisa Pegawai atau Ruangan
    public function penanggungJawab(): MorphTo
    {
        return $this->morphTo('penanggung_jawab');
    }

    public function getNupLabelAttribute(): string
    {
        return $this->kode_barang . ' / ' . $this->nup;
    }
}
