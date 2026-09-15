<?php

namespace App\Exports;

use App\Models\Sp2dRekap;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class Sp2dRekapPerBulanSheet extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder, WithColumnFormatting, WithStyles, WithTitle
{
    protected $query;
    protected $monthName;

    public function __construct(Builder $query, string $monthName)
    {
        $this->query = $query;
        $this->monthName = $monthName;
    }

    public function query()
    {
        return clone $this->query;
    }

    public function title(): string
    {
        return $this->monthName;
    }

    public function headings(): array
    {
        return [
            'No SP2D',
            'Tgl SP2D',
            'Jenis SPM',
            'Jalur Transaksi',
            'Bruto (Pengeluaran)',
            'Total Potongan',
            'Netto (Pembayaran)',
            'Status Verifikasi',
            'Uraian SPM',
        ];
    }

    public function map($row): array
    {
        return [
            $row->no_sp2d,
            $row->tgl_sp2d ? \Carbon\Carbon::parse($row->tgl_sp2d)->format('d-m-Y') : '',
            $row->jenis_spm,
            match ($row->jalur_transaksi) {
                '1_pihak' => '1 Pihak',
                'banyak_pihak' => 'Banyak Pihak',
                'up' => 'UP',
                default => (string) $row->jalur_transaksi,
            },
            $row->jumlah_pengeluaran ? (float) $row->jumlah_pengeluaran : 0,
            $row->jumlah_potongan   ? (float) $row->jumlah_potongan   : 0,
            $row->jumlah_pembayaran ? (float) $row->jumlah_pembayaran : 0,
            $row->status_verifikasi == 'valid' ? 'Valid' : 'Perlu Rincian',
            $row->uraian,
        ];
    }

    /**
     * Format kolom E, F, G sebagai Rupiah: Rp #.##0
     * Titik sebagai pemisah ribuan (locale IDR).
     */
    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
        ];
    }

    /**
     * Tebalkan baris header dan right-align kolom nominal.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value) && strlen((string)$value) > 12) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }
}
