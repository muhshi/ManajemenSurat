<?php

namespace App\Exports;

use App\Models\Sp2dPajak;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class Sp2dCoretaxExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected ?string $periodeBulan;
    protected string $periodeTahun;
    protected int $rowNumber = 0;

    public function __construct(?string $periodeBulan, string $periodeTahun)
    {
        $this->periodeBulan = $periodeBulan;
        $this->periodeTahun = $periodeTahun;
    }

    public function collection()
    {
        // Ambil semua pajak dari SP2D yang sudah valid di periode tsb
        $pajaks = Sp2dPajak::whereHas('rekap', function ($q) {
            $q->where('status_verifikasi', 'valid')
              ->whereYear('tgl_sp2d', $this->periodeTahun);
              
            if ($this->periodeBulan) {
                $q->whereMonth('tgl_sp2d', $this->periodeBulan);
            }
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
            
            // Rujukan SP2D (prepend with space to prevent Excel scientific notation)
            $sp2dList = $items->pluck('rekap.no_sp2d')->unique()->implode(', ');
            $sp2dList = $sp2dList ? ' ' . $sp2dList : null;

            $result->push([
                'npwp_nik' => $npwpNik,
                'nama_pihak' => $namaPihak,
                'pph21' => $pph21 ? (float)$pph21 : null,
                'pph22' => $pph22 ? (float)$pph22 : null,
                'pph23' => $pph23 ? (float)$pph23 : null,
                'ppn' => $ppn ? (float)$ppn : null,
                'pph_final' => $pphFinal ? (float)$pphFinal : null,
                'total' => $total ? (float)$total : null,
                'rujukan' => $sp2dList,
            ]);
        }

        if ($result->isNotEmpty()) {
            $result->push([
                'npwp_nik' => '',
                'nama_pihak' => 'GRAND TOTAL',
                'pph21' => $result->sum('pph21') ?: null,
                'pph22' => $result->sum('pph22') ?: null,
                'pph23' => $result->sum('pph23') ?: null,
                'ppn' => $result->sum('ppn') ?: null,
                'pph_final' => $result->sum('pph_final') ?: null,
                'total' => $result->sum('total') ?: null,
                'rujukan' => '',
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
        if ($row['nama_pihak'] === 'GRAND TOTAL') {
            return [
                '',
                '',
                'GRAND TOTAL',
                $row['pph21'],
                $row['pph22'],
                $row['pph23'],
                $row['ppn'],
                $row['pph_final'],
                $row['total'],
                ''
            ];
        }

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

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0', // PPH 21
            'E' => '#,##0', // PPH 22
            'F' => '#,##0', // PPH 23
            'G' => '#,##0', // PPN
            'H' => '#,##0', // PPH FINAL
            'I' => '#,##0', // TOTAL POTONGAN
            'J' => NumberFormat::FORMAT_TEXT, // NO. SP2D / RUJUKAN
        ];
    }
}
