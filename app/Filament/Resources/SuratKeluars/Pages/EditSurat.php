<?php

namespace App\Filament\Resources\SuratKeluars\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SuratKeluars\SuratKeluarResource;
use App\Services\TemplateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSurat extends EditRecord
{
    protected static string $resource = SuratKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
