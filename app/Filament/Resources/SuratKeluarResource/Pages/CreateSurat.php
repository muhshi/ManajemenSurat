<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Filament\Resources\SuratKeluarResource;
use App\Services\TemplateService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class CreateSurat extends CreateRecord
{
    protected static string $resource = SuratKeluarResource::class;
}
