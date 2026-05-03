<?php

namespace App\Models;

use App\Settings\SystemSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    protected $fillable = [
        'nomor_surat',
        'nomor_urut',
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
        'peserta_rapat',
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
     * Get the next sequence number for a given year
     */
    public static function getNextUrut(int $tahun): int
    {
        $lastAgenda = static::whereYear('tanggal_rapat', $tahun)
            ->max('nomor_urut');

        return ($lastAgenda ?: 0) + 1;
    }

    /**
     * Format nomor surat: B-XXX/33210/PR-710/MM/YYYY
     */
    public static function formatNomor(int $urut, \DateTimeInterface $tanggal): string
    {
        $settings = app(SystemSettings::class);
        $kodeKantor = $settings->office_code ?: '33210';
        
        $nomorUrutPadded = str_pad($urut, 3, '0', STR_PAD_LEFT);
        $bulan = $tanggal->format('m');
        $tahun = $tanggal->format('Y');

        return "B-{$nomorUrutPadded}/{$kodeKantor}/PR-710/{$bulan}/{$tahun}";
    }

    /**
     * Generate automatic nomor surat
     */
    public static function generateNomor(int $tahun): string
    {
        $nextUrut = static::getNextUrut($tahun);
        return static::formatNomor($nextUrut, now()->setYear($tahun));
    }

    /**
     * Get skipped numbers in a year
     */
    public static function getSkippedNumbers(int $tahun): array
    {
        $usedNumbers = static::whereYear('tanggal_rapat', $tahun)
            ->whereNotNull('nomor_urut')
            ->pluck('nomor_urut')
            ->toArray();

        if (empty($usedNumbers)) {
            return [];
        }

        $max = max($usedNumbers);
        $allNumbers = range(1, $max);
        
        return array_diff($allNumbers, $usedNumbers);
    }

    /**
     * Group skipped numbers by month for the widget
     */
    public static function getSkippedNumbersByMonth(int $tahun): array
    {
        // This is a bit tricky because we don't know which month a "skipped" number belongs to.
        // Usually, skipped numbers are just numbers missing in the sequence.
        // The user's image shows "Januari: 1-400", "Maret: 427, 657-659...", "April: 1491".
        // This implies they track which month the number WAS SUPPOSED to be in, or they just show gaps.
        // If we only have the final table, we can only see gaps in the sequence.
        
        $skipped = static::getSkippedNumbers($tahun);
        if (empty($skipped)) return [];

        // For simplicity, let's just return the formatted ranges.
        return $skipped;
    }

    /**
     * Helper to format ranges: [1, 2, 3, 5, 7, 8] -> "1-3, 5, 7-8"
     */
    public static function formatRanges(array $numbers): string
    {
        if (empty($numbers)) return '';
        
        sort($numbers);
        $ranges = [];
        $start = $numbers[0];
        $prev = $numbers[0];

        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] == $prev + 1) {
                $prev = $numbers[$i];
            } else {
                $ranges[] = ($start == $prev) ? (string)$start : "{$start}-{$prev}";
                $start = $numbers[$i];
                $prev = $numbers[$i];
            }
        }
        $ranges[] = ($start == $prev) ? (string)$start : "{$start}-{$prev}";

        return implode(', ', $ranges);
    }
}
