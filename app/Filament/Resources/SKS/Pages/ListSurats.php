<?php

namespace App\Filament\Resources\SKS\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SKS\SKResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurats extends ListRecords
{
    protected static string $resource = SKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
