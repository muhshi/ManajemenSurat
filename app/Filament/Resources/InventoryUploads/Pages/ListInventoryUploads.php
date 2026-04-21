<?php

namespace App\Filament\Resources\InventoryUploads\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\InventoryUploads\Widgets\UploadProgressWidget;
use App\Filament\Resources\InventoryUploads\InventoryUploadResource;
use App\Jobs\ProcessInventoryUpload;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryUploads extends ListRecords
{
    protected static string $resource = InventoryUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function ($record) {
                    // Set status to pending and dispatch background job
                    $record->update(['status' => 'pending']);
                    ProcessInventoryUpload::dispatch($record);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UploadProgressWidget::class,
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        return '2s';
    }
}
