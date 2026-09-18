<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPerPihakExport implements WithMultipleSheets
{
    use Exportable;

    protected array $months;

    /**
     * @param array $months Array of ['sheetTitle' => string, 'rows' => array[]]
     */
    public function __construct(array $months)
    {
        $this->months = $months;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->months as $monthData) {
            $sheets[] = new RekapPerPihakSheetExport(
                $monthData['sheetTitle'],
                $monthData['rows'],
                $monthData['nominalStartCol'] ?? 2,
                $monthData['cellComments'] ?? []
            );

            if (!empty($monthData['detailSheet'])) {
                $sheets[] = new RekapPerPihakDetailSheetExport(
                    $monthData['detailSheet']['sheetTitle'],
                    $monthData['detailSheet']['rows']
                );
            }
        }

        return $sheets;
    }
}
