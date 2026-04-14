<?php

namespace App\Filament\Resources\PermintaanBarangs\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PermintaanBarangs\PermintaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanBarang extends EditRecord
{
    protected static string $resource = PermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
