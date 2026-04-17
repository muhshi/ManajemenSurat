<?php

namespace App\Filament\Resources\Sp2dRekapResource\Pages;

use App\Filament\Resources\Sp2dRekapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use App\Models\Sp2dUpload;
use App\Jobs\ProcessSp2dImport;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Sp2dRekapResource\Widgets\Sp2dUploadProgressWidget;

class ListSp2dRekaps extends ListRecords
{
    protected static string $resource = Sp2dRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import SP2D')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('filename')
                        ->label('File Excel SP2D')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->directory('sp2d-uploads')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $upload = Sp2dUpload::create([
                        'filename' => $data['filename'],
                        'periode' => 'PENDING',
                        'status' => 'processing',
                        'uploaded_by' => Auth::id(),
                    ]);

                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($upload->filename);
                    \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\Sp2dImport($upload->id), $filePath);

                    \Filament\Notifications\Notification::make()
                        ->title('Import Selesai')
                        ->body('Data SP2D telah berhasil dibaca dan dimasukkan ke tabel.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Sp2dRekapResource\Widgets\Sp2dUploadsTableWidget::class,
        ];
    }
}
