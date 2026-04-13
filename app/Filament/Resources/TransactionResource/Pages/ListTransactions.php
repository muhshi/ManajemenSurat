<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_kartu_kendali')
                ->label('Export Kartu Kendali')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\InventoryCardsExport(), 
                        'Kartu_Kendali_Persediaan_' . date('Y_m_d_H_i_s') . '.xlsx'
                    );
                }),
            Actions\CreateAction::make(),
        ];
    }
}
