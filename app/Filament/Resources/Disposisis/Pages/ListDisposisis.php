<?php

namespace App\Filament\Resources\Disposisis\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Disposisis\DisposisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisposisis extends ListRecords
{
    protected static string $resource = DisposisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
