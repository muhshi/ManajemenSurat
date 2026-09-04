<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class RekapPerPihakExport extends DefaultValueBinder implements FromArray, WithCustomValueBinder
{
    use Exportable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Hindari scientific notation untuk NPWP/NIK (di kolom ke-2 biasanya)
        // Panjang NPWP/NIK adalah 15-16 karakter.
        if (is_numeric(str_replace(['.', '-'], '', $value)) && strlen((string)$value) >= 15) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
