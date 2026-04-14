<?php

namespace App\Filament\Resources\InventoryUploads\Widgets;

use App\Models\InventoryUpload;
use Filament\Widgets\Widget;

class UploadProgressWidget extends Widget
{
    protected string $view = 'filament.resources.inventory-upload-resource.widgets.upload-progress-widget';

    protected int | string | array $columnSpan = 'full';

    public function getActiveUploads()
    {
        // Get the latest 1 upload to show in widget
        return InventoryUpload::latest('id')->take(1)->get();
    }
}
