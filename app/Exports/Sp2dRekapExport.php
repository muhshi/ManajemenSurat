<?php

namespace App\Exports;

use App\Models\Sp2dRekap;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class Sp2dRekapExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return clone $this->query;
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
            $row->jalur_transaksi,
            $row->jumlah_pengeluaran,
            $row->jumlah_potongan,
            $row->jumlah_pembayaran,
            $row->status_verifikasi == 'valid' ? 'Valid' : 'Perlu Rincian',
            $row->uraian,
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
