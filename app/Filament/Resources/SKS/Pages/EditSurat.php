<?php

namespace App\Filament\Resources\SKS\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SKS\SKResource;
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
            DeleteAction::make(),
        ];
    }
}
