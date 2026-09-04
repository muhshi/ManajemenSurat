<?php

namespace App\Filament\Resources\KodeSpms\Pages;

use App\Filament\Resources\KodeSpms\KodeSpmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKodeSpms extends ListRecords
{
    protected static string $resource = KodeSpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
