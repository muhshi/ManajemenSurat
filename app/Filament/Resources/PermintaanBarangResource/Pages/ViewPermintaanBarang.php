<?php

namespace App\Filament\Resources\PermintaanBarangResource\Pages;

use App\Filament\Resources\PermintaanBarangResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewPermintaanBarang extends ViewRecord
{
    protected static string $resource = PermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
