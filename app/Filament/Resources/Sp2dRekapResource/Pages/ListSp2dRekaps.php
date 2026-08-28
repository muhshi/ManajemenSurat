<?php

namespace App\Filament\Resources\Sp2dRekapResource\Pages;

use App\Filament\Resources\Sp2dRekapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use App\Models\Sp2dUpload;
use App\Jobs\ProcessSp2dImport;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListSp2dRekaps extends ListRecords
{
    protected static string $resource = Sp2dRekapResource::class;

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString(
            'Data Rekap SP2D <style>.fi-ta-content { transform: rotateX(180deg); } .fi-ta-content > table, .fi-ta-content > div { transform: rotateX(180deg); }</style>'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Segarkan Data')
                ->icon('heroicon-o-arrow-path')
                ->color('secondary')
                ->action(fn () => null),
            Actions\Action::make('export_coretax')
                ->label('Export Rekap SP2D')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('periode_bulan')
                        ->label('Periode Bulan')
                        ->options([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ])
                        ->placeholder('Semua Bulan (Setahun)')
                        ->default(null),
                    Select::make('periode_tahun')
                        ->label('Periode Tahun')
                        ->options(array_combine(range(date('Y')-2, date('Y')+1), range(date('Y')-2, date('Y')+1)))
                        ->default(date('Y'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('sp2d.export.coretax', [
                        'bulan' => $data['periode_bulan'],
                        'tahun' => $data['periode_tahun']
                    ]);
                }),

            Actions\Action::make('import')
                ->label('Import SP2D MyIntress')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    Select::make('periode_tahun')
                        ->label('Periode Tahun')
                        ->options(array_combine(range(date('Y')-2, date('Y')+1), range(date('Y')-2, date('Y')+1)))
                        ->default(date('Y'))
                        ->required(),
                    FileUpload::make('file_monitoring_sp2d')
                        ->label('1. File Monitoring SPP, SPM, dan SP2D')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->directory('sp2d-uploads')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('file_potongan_spm')
                        ->label('2. File Monitoring Potongan SPM (Opsional)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->directory('sp2d-uploads')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $upload = Sp2dUpload::create([
                        'file_monitoring_sp2d' => $data['file_monitoring_sp2d'],
                        'file_potongan_spm' => $data['file_potongan_spm'] ?? null,
                        'periode_bulan' => null,
                        'periode_tahun' => $data['periode_tahun'],
                        'status' => 'processing',
                        'user_id' => Auth::id(),
                    ]);

                    ProcessSp2dImport::dispatch($upload);

                    Notification::make()
                        ->title('Import Diproses')
                        ->body('Data sedang diproses di latar belakang. Silakan refresh halaman beberapa saat lagi.')
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
