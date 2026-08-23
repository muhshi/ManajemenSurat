<?php

namespace App\Filament\Resources\Sp2dRekapResource\Pages;

use App\Filament\Resources\Sp2dRekapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSp2dRekap extends EditRecord
{
    protected static string $resource = Sp2dRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        // Custom validation logic
        $record = $this->record;
        
        // Cek jika status valid dan ini adalah jalur banyak pihak, pastikan nominal match
        if ($this->data['status_verifikasi'] === 'valid' && $record->jalur_transaksi === 'banyak_pihak') {
            $totalPajakTerinput = collect($this->data['pajaks'] ?? [])->sum('nominal_pajak');
            
            if ($totalPajakTerinput != $record->jumlah_potongan) {
                Notification::make()
                    ->danger()
                    ->title('Validasi Gagal')
                    ->body("Total pajak terinput (Rp " . number_format($totalPajakTerinput, 0, ',', '.') . 
                           ") tidak sama dengan Target Potongan (Rp " . number_format($record->jumlah_potongan, 0, ',', '.') . ").")
                    ->send();
                    
                $this->halt();
            }
        }
    }
}
