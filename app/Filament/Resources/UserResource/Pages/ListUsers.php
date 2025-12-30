<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        if (!auth()->user()->hasRole('super_admin')) {
            redirect(UserResource::getUrl('edit', ['record' => auth()->id()]));
            return;
        }
        parent::mount();
    }
}
