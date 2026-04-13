<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\InventoryItemSheet;

class InventoryCardsExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;

    public function __construct($year = null)
    {
        $this->year = $year ?? date('Y');
    }

    public function sheets(): array
    {
        $sheets = [];
        $year = $this->year;

        // Ambil item yang memiliki transaksi di tahun tersebut
        $items = Item::whereHas('transactions', function ($q) use ($year) {
                $q->whereYear('tanggal', $year);
            })
            ->with(['transactions' => function ($query) {
                // Ambil SEMUA transaksi item (untuk hitung carry-over saldo awal & rincian)
                $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
            }])
            ->get();

        $index = 1;
        foreach ($items as $item) {
            $sheets[] = new InventoryItemSheet($item, $this->year, $index);
            $index++;
        }

        return $sheets;
    }
}
