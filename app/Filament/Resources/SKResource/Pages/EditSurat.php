<?php

namespace App\Filament\Resources\SKResource\Pages;

use App\Filament\Resources\SKResource;
use App\Services\TemplateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSurat extends EditRecord
{
    protected static string $resource = SKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
