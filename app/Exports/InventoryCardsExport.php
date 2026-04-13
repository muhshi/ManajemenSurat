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
        
        // Ambil hanya item yang memiliki transaksi
        $items = Item::whereHas('transactions')
            ->with(['transactions' => function ($query) {
                $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
            }])->get();

        $index = 1;
        foreach ($items as $item) {
            // Buat 1 sheet untuk setiap item (barang)
            $sheets[] = new InventoryItemSheet($item, $this->year, $index);
            $index++;
        }

        return $sheets;
    }
}
