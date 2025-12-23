<?php

namespace App\Filament\Resources\SKResource\Pages;

use App\Filament\Resources\SKResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurats extends ListRecords
{
    protected static string $resource = SKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
