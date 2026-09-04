<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunPajak extends Model
{
    protected $fillable = [
        'kode',
        'nama_pendek',
        'nama_lengkap',
    ];
}
