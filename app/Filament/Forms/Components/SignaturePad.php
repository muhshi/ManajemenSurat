<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SignaturePad extends Field
{
    protected string $view = 'filament.forms.components.signature-pad';

    /**
     * Check if the form is in view-only mode (infolist / disabled).
     */
    public function isViewMode(): bool
    {
        try {
            $operation = $this->getContainer()->getOperation();
            return in_array($operation, ['view', 'viewRecord']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
