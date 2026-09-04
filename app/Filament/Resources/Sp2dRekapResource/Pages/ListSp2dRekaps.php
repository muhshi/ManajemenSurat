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

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Sp2dRekapResource\Widgets\Sp2dRekapStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\Sp2dRekapExport($query),
                            'Data_Rekap_SP2D_' . date('Ymd_His') . '.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    }),
                \Filament\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-chart-bar')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\Sp2dRekapExport($query),
                            'Data_Rekap_SP2D_' . date('Ymd_His') . '.xlsx',
                            \Maatwebsite\Excel\Excel::XLSX
                        );
                    }),
                \Filament\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        $records = clone $query->get();
                        
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.sp2d-rekap', [
                            'records' => $records
                        ])->setPaper('a4', 'landscape');
                        
                        return response()->streamDownload(fn () => print($pdf->output()), 'Data_Rekap_SP2D_' . date('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->button(),

            Actions\Action::make('refresh')
                ->label('Segarkan Data')
                ->icon('heroicon-o-arrow-path')
                ->color('secondary')
                ->action(fn () => null),

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
                        'file_monitoring_sp2d' => $data['file_monitoring_sp2d'] ?? null,
                        'file_potongan_spm' => $data['file_potongan_spm'] ?? null,
                        'periode_bulan' => null,
                        'periode_tahun' => $data['periode_tahun'],
                        'status' => 'processing',
                        'user_id' => Auth::id(),
                    ]);

                    ProcessSp2dImport::dispatchSync($upload);

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
