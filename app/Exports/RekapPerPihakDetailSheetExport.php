<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPerPihakDetailSheetExport extends DefaultValueBinder implements FromArray, WithTitle, WithCustomValueBinder, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected string $sheetTitle;
    protected array $rows;

    public function __construct(string $sheetTitle, array $rows)
    {
        $this->sheetTitle = substr($sheetTitle, 0, 31);
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

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // Hindari format scientific untuk NPWP, NIK, atau nomor SP2D panjang
        if (is_numeric(str_replace(['.', '-', '/', ' '], '', (string)$value)) && strlen((string)$value) >= 15) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
