<?php

namespace App\Filament\Resources\SuratKeluars\Pages;

use App\Filament\Resources\SuratKeluars\SuratKeluarResource;
use App\Services\TemplateService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class CreateSurat extends CreateRecord
{
    protected static string $resource = SuratKeluarResource::class;
}
