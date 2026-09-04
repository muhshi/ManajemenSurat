<?php

namespace App\Filament\Resources\AkunPajakResource\Pages;

use App\Filament\Resources\AkunPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAkunPajaks extends ListRecords
{
    protected static string $resource = AkunPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
