<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaPeserta extends Model
{
    protected $table = 'agenda_pesertas';

    protected $fillable = [
        'agenda_id',
        'nama',
        'jabatan',
        'no_hp',
        'hadir',
        'urutan',
    ];

    protected $casts = [
        'hadir' => 'boolean',
        'urutan' => 'integer',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }
}
