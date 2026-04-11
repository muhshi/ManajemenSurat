<?php

namespace App\Filament\Resources\InventoryUploadResource\Pages;

use App\Filament\Resources\InventoryUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryUploads extends ListRecords
{
    protected static string $resource = InventoryUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryUploadResource\Widgets\UploadProgressWidget::class,
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        return '2s';
    }
}
