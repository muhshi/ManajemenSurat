<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = [
        'nomor_urut',
        'nomor_surat',
        'judul_surat',
        'jenis_surat',
        'tanggal_surat',
        'tahun',
        'file_path',
        'signer_name',
        'signer_nip',
        'signer_title',
        'signer_city',
        'kepada',
        'perihal',
        'klasifikasi_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tahun' => 'integer',
        'nomor_urut' => 'integer',
        'klasifikasi_id' => 'integer',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    /**
     * Get the next nomor urut for a given year and type (suggestion only)
     */
    public static function getNextNomorUrut(int $tahun, string $jenisSurat = 'Surat Keluar'): int
    {
        $query = static::where('tahun', $tahun);

        if ($jenisSurat === 'SK') {
            $query->where('jenis_surat', 'SK');
        } else {
            // Surat Keluar, Memo, Surat Pengantar share numbering
            $query->whereIn('jenis_surat', ['Surat Keluar', 'Memo', 'Surat Pengantar']);
        }

        $lastSurat = $query->orderBy('nomor_urut', 'desc')->first();

        return $lastSurat ? $lastSurat->nomor_urut + 1 : 1;
    }

    /**
     * Generate nomor surat format
     * SK: 0001/33210/KP.650/2025
     * Others: B-0001/33210/KP.650/2025
     */
    public static function generateNomorSurat(int $nomorUrut, int $tahun, string $jenisSurat = 'Surat Keluar', string $klasifikasi = 'KP.650'): string
    {
        $settings = app(\App\Settings\SystemSettings::class);
        $nomorUrutPadded = str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);
        $kodeKantor = $settings->office_code ?: '33210';

        if ($jenisSurat === 'SK') {
            // Format: XXX/3321/KPA TAHUN 2025
            // Note: User wrote 3321 in prompt, but office_code is 33210. I'll use the setting.
            // Using 3-digit padded for XXX might be better if they want XXX, but str_pad(4) was there.
            // Let's use 3-digit for SK as per "XXX" and 4-digit for others as per "XXXX".
            $nomorUrut3 = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            return "{$nomorUrut3}/{$kodeKantor}/KPA TAHUN {$tahun}";
        }

        // Default prefix for others
        // Format: B-XXXX/33210/YY.YYY/2025
        $prefix = 'B';

        return "{$prefix}-{$nomorUrutPadded}/{$kodeKantor}/{$klasifikasi}/{$tahun}";
    }
}
