<?php

namespace App\Exports;

use App\Models\Sp2dPajak;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class Sp2dCoretaxExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $periodeBulan;
    protected string $periodeTahun;
    protected int $rowNumber = 0;

    public function __construct(string $periodeBulan, string $periodeTahun)
    {
        $this->periodeBulan = $periodeBulan;
        $this->periodeTahun = $periodeTahun;
    }

    public function collection()
    {
        // Ambil semua pajak dari SP2D yang sudah valid di periode tsb
        $pajaks = Sp2dPajak::whereHas('rekap.upload', function ($q) {
            $q->where('periode_bulan', $this->periodeBulan)
              ->where('periode_tahun', $this->periodeTahun);
        })
        ->whereHas('rekap', function ($q) {
            $q->where('status_verifikasi', 'valid');
        })
        ->with('rekap')
        ->get();

        // Group by NPWP/NIK + Nama Pihak
        $grouped = $pajaks->groupBy(function ($item) {
            return $item->npwp_nik . '|' . $item->nama_pihak;
        });

        $result = collect();

        foreach ($grouped as $key => $items) {
            list($npwpNik, $namaPihak) = explode('|', $key);

            $pph21 = $items->whereIn('kode_akun_pajak', ['411121'])->sum('nominal_pajak');
            $pph22 = $items->whereIn('kode_akun_pajak', ['411122'])->sum('nominal_pajak');
            $pph23 = $items->whereIn('kode_akun_pajak', ['411124'])->sum('nominal_pajak');
            $ppn = $items->whereIn('kode_akun_pajak', ['411211'])->sum('nominal_pajak');
            $pphFinal = $items->whereIn('kode_akun_pajak', ['411128'])->sum('nominal_pajak');
            $total = $items->sum('nominal_pajak');
            
            // Rujukan SP2D
            $sp2dList = $items->pluck('rekap.no_sp2d')->unique()->implode(', ');

            $result->push([
                'npwp_nik' => $npwpNik,
                'nama_pihak' => $namaPihak,
                'pph21' => $pph21,
                'pph22' => $pph22,
                'pph23' => $pph23,
                'ppn' => $ppn,
                'pph_final' => $pphFinal,
                'total' => $total,
                'rujukan' => $sp2dList,
            ]);
        }

        return $result;
    }

    public function headings(): array
    {
        return [
            'NO',
            'NPWP / NIK',
            'NAMA PIHAK',
            'PPH 21',
            'PPH 22',
            'PPH 23',
            'PPN',
            'PPH FINAL',
            'TOTAL POTONGAN',
            'NO. SP2D / RUJUKAN'
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $row['npwp_nik'],
            $row['nama_pihak'],
            $row['pph21'],
            $row['pph22'],
            $row['pph23'],
            $row['ppn'],
            $row['pph_final'],
            $row['total'],
            $row['rujukan']
        ];
    }
}
