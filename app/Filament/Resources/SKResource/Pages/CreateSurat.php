<?php

namespace App\Filament\Resources\SKResource\Pages;

use App\Filament\Resources\SKResource;
use App\Services\TemplateService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class CreateSurat extends CreateRecord
{
    protected static string $resource = SKResource::class;
}
