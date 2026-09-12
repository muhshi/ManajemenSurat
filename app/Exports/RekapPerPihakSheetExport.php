<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class RekapPerPihakSheetExport extends DefaultValueBinder implements FromArray, WithTitle, WithCustomValueBinder
{
    use Exportable;

    protected string $sheetTitle;
    protected array $rows;

    public function __construct(string $sheetTitle, array $rows)
    {
        $this->sheetTitle = $sheetTitle;
        $this->rows = $rows;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Hindari scientific notation untuk NPWP/NIK
        if (is_numeric(str_replace(['.', '-'], '', (string)$value)) && strlen((string)$value) >= 15) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
