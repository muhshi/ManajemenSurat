<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Database\Eloquent\Builder;

class Sp2dRekapExport implements WithMultipleSheets
{
    use Exportable;

    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Dapatkan semua bulan unik dari query hasil filter
        $clone = clone $this->query;
        $months = $clone->selectRaw('DATE_FORMAT(tgl_sp2d, "%Y-%m") as sort_key, DATE_FORMAT(tgl_sp2d, "%m") as bulan, DATE_FORMAT(tgl_sp2d, "%Y") as tahun')
            ->whereNotNull('tgl_sp2d')
            ->distinct()
            ->orderBy('sort_key', 'asc')
            ->get();

        if ($months->isEmpty()) {
            // Jika tidak ada data tanggal, buat 1 sheet kosong atau fallback
            $sheets[] = new Sp2dRekapPerBulanSheet(clone $this->query, 'Data_SP2D');
            return $sheets;
        }

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        foreach ($months as $monthData) {
            $sheetQuery = (clone $this->query)
                ->whereYear('tgl_sp2d', $monthData->tahun)
                ->whereMonth('tgl_sp2d', $monthData->bulan);
            
            $bulanName = $namaBulan[$monthData->bulan] ?? $monthData->bulan;
            $sheetName = $monthData->tahun . '_' . $monthData->bulan . '_' . $bulanName;
            $sheets[] = new Sp2dRekapPerBulanSheet($sheetQuery, $sheetName);
        }

        return $sheets;
    }
}
