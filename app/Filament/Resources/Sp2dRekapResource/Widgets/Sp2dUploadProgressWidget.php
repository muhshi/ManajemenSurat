<?php

namespace App\Filament\Resources\Sp2dRekapResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Sp2dUpload;

class Sp2dUploadProgressWidget extends Widget
{
    protected string $view = 'filament.widgets.sp2d-upload-progress';

    // The current active upload (if any)
    public ?Sp2dUpload $currentUpload = null;

    protected int | string | array $columnSpan = 'full';

    public function mount()
    {
        $this->loadCurrentUpload();
    }

    public function loadCurrentUpload()
    {
        // Get the latest upload that is not 'done' or 'failed', 
        // or if it was recently finished (e.g. created in the last 15 minutes)
        $this->currentUpload = Sp2dUpload::latest()
            ->first();

        // If there's no upload or the latest is very old and already done/failed, don't show the widget progress details
        // Actually, let's always show the very latest upload's log, users can dismiss it or we just keep it as terminal
    }

    /**
     * Called by Livewire polling every 2 seconds via wire:poll in the view.
     */
    public function updateProgress()
    {
        if ($this->currentUpload && in_array($this->currentUpload->status, ['pending', 'processing'])) {
            $this->currentUpload->refresh();
        } else {
            // Find if there's a new one while polling
            $this->loadCurrentUpload();
        }
    }
}
