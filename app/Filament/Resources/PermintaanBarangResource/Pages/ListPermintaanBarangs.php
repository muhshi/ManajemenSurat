<?php

namespace App\Filament\Resources\PermintaanBarangResource\Pages;

use App\Filament\Resources\PermintaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanBarangs extends ListRecords
{
    protected static string $resource = PermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
