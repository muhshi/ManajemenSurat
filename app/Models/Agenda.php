<?php

namespace App\Models;

use App\Settings\SystemSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    protected $fillable = [
        'nomor_surat',
        'judul',
        'perihal',
        'penerima_undangan',
        'tempat',
        'tanggal_rapat',
        'waktu_mulai',
        'waktu_selesai',
        'pimpinan_rapat',
        'narasumber',
        'notulis',
        'isi_notulensi',
        'keputusan',
        'tindak_lanjut',
        'signer_name',
        'signer_nip',
        'signer_title',
        'signer_city',
        'status',
    ];

    protected $casts = [
        'tanggal_rapat' => 'date',
    ];

    public function peserta(): HasMany
    {
        return $this->hasMany(AgendaPeserta::class);
    }

    /**
     * Generate nomor surat format: ND-XXXX/33210/2026
     */
    public static function generateNomor(int $tahun): string
    {
        $settings = app(SystemSettings::class);
        $kodeKantor = $settings->office_code ?: '33210';

        $lastAgenda = static::whereYear('tanggal_rapat', $tahun)
            ->orderBy('id', 'desc')
            ->first();

        $nextUrut = 1;
        if ($lastAgenda) {
            // Extract XXXX from ND-XXXX/...
            $parts = explode('/', $lastAgenda->nomor_surat);
            if (isset($parts[0])) {
                $subParts = explode('-', $parts[0]);
                if (isset($subParts[1])) {
                    $nextUrut = (int) $subParts[1] + 1;
                }
            }
        }

        $nomorUrutPadded = str_pad($nextUrut, 4, '0', STR_PAD_LEFT);

        return "ND-{$nomorUrutPadded}/{$kodeKantor}/{$tahun}";
    }
}
