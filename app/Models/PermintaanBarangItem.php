<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanBarangItem extends Model
{
    protected $guarded = ['id'];

    public function permintaanBarang(): BelongsTo
    {
        return $this->belongsTo(PermintaanBarang::class);
    }
}
