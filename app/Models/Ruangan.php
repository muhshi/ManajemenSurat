<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Ruangan extends Model
{
    protected $fillable = [
        'kode_ruang',
        'nama_tipe_ruang',
        'nama_ruang',
        'lantai',
        'luas_ruang',
        'gedung',
    ];

    protected $casts = [
        'lantai' => 'integer',
        'luas_ruang' => 'decimal:2',
    ];

    // BMN yang berlokasi di ruangan ini
    public function bmns(): HasMany
    {
        return $this->hasMany(Bmn::class, 'ruangan_id');
    }

    // BMN yang ruangan ini sebagai penanggung jawab
    public function bmnsAsPeran(): MorphMany
    {
        return $this->morphMany(Bmn::class, 'penanggung_jawab');
    }

    public function getLabelAttribute(): string
    {
        return $this->kode_ruang . ' - ' . $this->nama_ruang;
    }
}
