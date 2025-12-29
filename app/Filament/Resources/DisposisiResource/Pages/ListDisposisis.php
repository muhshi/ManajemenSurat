<?php

namespace App\Filament\Resources\DisposisiResource\Pages;

use App\Filament\Resources\DisposisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisposisis extends ListRecords
{
    protected static string $resource = DisposisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
