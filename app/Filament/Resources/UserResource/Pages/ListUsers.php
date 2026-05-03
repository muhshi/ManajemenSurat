<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_users')
                ->label('Sync dari Sipetra')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->action(fn () => \Illuminate\Support\Facades\Artisan::call('sync:users', ['--full' => true]))
                ->successNotificationTitle('Sinkronisasi selesai!'),
            Actions\CreateAction::make(),
        ];
    }
}
