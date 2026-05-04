<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use App\Jobs\SyncUsersJob;

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
                ->action(function () {
                    SyncUsersJob::dispatch(true);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Sinkronisasi Dimulai')
                        ->body('Proses sinkronisasi sedang berjalan di latar belakang (Background Job). Data akan terupdate otomatis dalam beberapa saat.')
                        ->info()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
