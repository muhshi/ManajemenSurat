<?php

namespace App\Filament\Resources\SuratMasuks\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SuratMasuks\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratMasuks extends ListRecords
{
    protected static string $resource = SuratMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
