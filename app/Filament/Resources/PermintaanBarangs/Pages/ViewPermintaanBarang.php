<?php

namespace App\Filament\Resources\PermintaanBarangs\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\PermintaanBarangs\PermintaanBarangResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewPermintaanBarang extends ViewRecord
{
    protected static string $resource = PermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
