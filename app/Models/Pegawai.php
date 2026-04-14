<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Pegawai extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'no_hp',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // BMN yang menjadi tanggung jawab pegawai ini
    public function bmns(): MorphMany
    {
        return $this->morphMany(Bmn::class, 'penanggung_jawab');
    }

    public function getDisplayNamaAttribute(): string
    {
        return $this->nip ? $this->nama . ' (' . $this->nip . ')' : $this->nama;
    }
}
