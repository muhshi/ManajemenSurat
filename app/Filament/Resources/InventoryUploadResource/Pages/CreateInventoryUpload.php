<?php

namespace App\Filament\Resources\InventoryUploadResource\Pages;

use App\Filament\Resources\InventoryUploadResource;
use Filament\Resources\Pages\CreateRecord;
use App\Jobs\ProcessInventoryUpload;

class CreateInventoryUpload extends CreateRecord
{
    protected static string $resource = InventoryUploadResource::class;

    protected function afterCreate(): void
    {
        $upload = $this->record;
        
        // Ensure status is marked as pending/processing initially
        $upload->update(['status' => 'pending']);
        
        // Dispatch the background job to handle the parsing
        ProcessInventoryUpload::dispatch($upload);
    }
}
