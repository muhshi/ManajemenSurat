<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPerPihakSheetExport extends DefaultValueBinder implements FromArray, WithTitle, WithCustomValueBinder, WithColumnFormatting, WithStyles
{
    use Exportable;

    protected string $sheetTitle;
    protected array $rows;
    protected int $nominalStartCol;  // indeks kolom pertama nominal (0-based dari header)

    public function __construct(string $sheetTitle, array $rows, int $nominalStartCol = 2)
    {
        $this->sheetTitle       = $sheetTitle;
        $this->rows             = $rows;
        $this->nominalStartCol  = $nominalStartCol;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function array(): array
    {
        return $this->rows;
    }

    /**
     * Format semua kolom mulai kolom C (indeks 2) ke kanan sebagai angka Rupiah.
     * Header baris ke-1 dikecualikan secara otomatis oleh PhpSpreadsheet
     * karena nilainya bukan numerik.
     *
     * Pola '#,##0' menggunakan pemisah ribuan sesuai locale Excel yang diset.
     * Di Excel Indonesia (Windows) koma otomatis menjadi titik.
     */
    public function columnFormats(): array
    {
        $formats = [];
        // Kolom A = 65, B = 66, mulai C = 67
        $startAscii = 65 + $this->nominalStartCol;
        // Asumsikan maks 30 kolom nominal
        for ($i = $startAscii; $i < $startAscii + 30; $i++) {
            $formats[chr($i)] = '#,##0';
        }
        return $formats;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
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

