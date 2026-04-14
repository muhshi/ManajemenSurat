<?php

namespace App\Filament\Resources\SuratMasuks\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SuratMasuks\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratMasuk extends EditRecord
{
    protected static string $resource = SuratMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
