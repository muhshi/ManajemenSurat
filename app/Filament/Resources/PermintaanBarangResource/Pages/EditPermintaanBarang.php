<?php

namespace App\Filament\Resources\PermintaanBarangResource\Pages;

use App\Filament\Resources\PermintaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanBarang extends EditRecord
{
    protected static string $resource = PermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
