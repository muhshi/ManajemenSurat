<?php

namespace App\Filament\Resources\Transactions\Pages;

use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryCardsExport;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_kartu_kendali')
                ->label('Export Kartu Kendali')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new InventoryCardsExport(), 
                        'Kartu_Kendali_Persediaan_' . date('Y_m_d_H_i_s') . '.xlsx'
                    );
                }),
            CreateAction::make(),
        ];
    }
}
