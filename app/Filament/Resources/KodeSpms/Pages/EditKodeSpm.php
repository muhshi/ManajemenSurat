<?php

namespace App\Filament\Resources\KodeSpms\Pages;

use App\Filament\Resources\KodeSpms\KodeSpmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKodeSpm extends EditRecord
{
    protected static string $resource = KodeSpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
