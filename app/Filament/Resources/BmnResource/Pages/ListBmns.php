<?php

namespace App\Filament\Resources\BmnResource\Pages;

use App\Filament\Resources\BmnResource;
use App\Imports\BmnImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListBmns extends ListRecords
{
    protected static string $resource = BmnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('import')
                ->label('Import dari SIMAN')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel SIMAN (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('bmn-imports'),
                ])
                ->action(function (array $data) {
                    $importer = new BmnImport();
                    Excel::import($importer, storage_path('app/private/' . $data['file']));

                    Notification::make()
                        ->title('Import Selesai')
                        ->body(
                            "✅ Baru: {$importer->imported} | " .
                            "🔄 Diperbarui: {$importer->updated} | " .
                            "⏭ Dilewati: {$importer->skipped}"
                        )
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}

