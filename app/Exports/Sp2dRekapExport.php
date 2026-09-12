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

        foreach ($months as $monthData) {
            $sheetQuery = (clone $this->query)
                ->whereYear('tgl_sp2d', $monthData->tahun)
                ->whereMonth('tgl_sp2d', $monthData->bulan);
            
            $sheetName = 'PERIODE_' . $monthData->bulan;
            $sheets[] = new Sp2dRekapPerBulanSheet($sheetQuery, $sheetName);
        }

        return $sheets;
    }
}
