<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2dUpload extends Model
{
    protected $fillable = [
        'filename',
        'periode',
        'total_rows',
        'status',
        'error_log',
        'uploaded_by',
    ];

    public function rekaps(): HasMany
    {
        return $this->hasMany(Sp2dRekap::class, 'upload_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
