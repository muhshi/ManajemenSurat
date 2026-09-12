<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Sp2dPajak;
use Filament\Support\Enums\Alignment;
use Filament\Actions\Action;

class RekapPerPihak extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Rekap Per Pihak';
    protected static ?string $title = 'Rekapitulasi Pajak Per Pihak';
    protected static string|\UnitEnum|null $navigationGroup = 'Rekap SP2D';
    protected static ?int $navigationSort = 3;

    public function getWidgetData(): array
    {
        return [
            'tableColumnSearches' => $this->tableColumnSearches,
            'tableFilters' => $this->tableFilters,
            'tableGrouping' => $this->tableGrouping,
            'tableRecordsPerPage' => $this->tableRecordsPerPage,
            'tableSearch' => $this->tableSearch,
            'tableSort' => $this->tableSort,
        ];
    }

    protected string $view = 'filament.pages.rekap-per-pihak';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RekapPajakOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        $akuns = \App\Models\AkunPajak::orderBy('kode')->get();

        $selectRaw = "
            MIN(id) as id,
            npwp_nik, 
            nama_pihak,
        ";

        foreach ($akuns as $akun) {
            $columnName = 'pajak_' . $akun->kode;
            $selectRaw .= "SUM(CASE WHEN kode_akun_pajak IN ('{$akun->kode}') THEN nominal_pajak ELSE 0 END) as {$columnName}, ";
        }

        $selectRaw .= "SUM(nominal_pajak) as total";

        $columns = [
            TextColumn::make('nama_pihak')
                ->label('Nama Pihak')
                ->searchable()
                ->sortable()
                ->wrap(),
            TextColumn::make('npwp_nik')
                ->label('NPWP / NIK')
                ->searchable(),
        ];

        $defaultVisible = ['411121', '411122', '411124', '411211', '411128'];

        foreach ($akuns as $akun) {
            $columnName = 'pajak_' . $akun->kode;
            $isHidden = !in_array($akun->kode, $defaultVisible);
            
            $columns[] = TextColumn::make($columnName)
                ->label($akun->kode . ' - ' . $akun->nama_pendek)
                ->formatStateUsing(fn ($state) => $state ? 'Rp' . number_format((float) $state, 0, ',', '.') : '-')
                ->alignment(Alignment::End)
                ->toggleable(isToggledHiddenByDefault: $isHidden);
        }

        $columns[] = TextColumn::make('total')
            ->label('Total Potongan')
            ->formatStateUsing(fn ($state) => $state ? 'Rp' . number_format((float) $state, 0, ',', '.') : '-')
            ->color('success')
            ->weight('bold')
            ->alignment(Alignment::End);

        return $table
            ->query(Sp2dPajak::query())
            ->modifyQueryUsing(function (Builder $query) use ($selectRaw) {
                $filterState = $this->getTableFilterState('periode') ?? [];
                $bulan = $filterState['bulan'] ?? null;
                $tahun = $filterState['tahun'] ?? null;
                $noSp2d = $filterState['no_sp2d'] ?? null;

                $subquery = Sp2dPajak::query()
                    ->selectRaw($selectRaw)
                    ->whereHas('rekap', function ($q) use ($bulan, $tahun, $noSp2d) {
                        $q->where('status_verifikasi', 'valid');
                        if ($bulan) {
                            $q->whereMonth('tgl_sp2d', $bulan);
                        }
                        if ($tahun) {
                            $q->whereYear('tgl_sp2d', $tahun);
                        }
                        if ($noSp2d) {
                            $q->where('no_sp2d', 'like', "%{$noSp2d}%");
                        }
                    })
                    ->groupBy('npwp_nik', 'nama_pihak');

                return $query->fromSub($subquery, 'sp2d_pajaks');
            })
            ->columns($columns)
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                \Filament\Forms\Components\Select::make('bulan')
                                    ->label('Bulan')
                                    ->options([
                                        '01' => 'Januari',
                                        '02' => 'Februari',
                                        '03' => 'Maret',
                                        '04' => 'April',
                                        '05' => 'Mei',
                                        '06' => 'Juni',
                                        '07' => 'Juli',
                                        '08' => 'Agustus',
                                        '09' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ])
                                    ->placeholder('Semua Bulan'),
                                \Filament\Forms\Components\Select::make('tahun')
                                    ->label('Tahun / Periode')
                                    ->options(function () {
                                        return \App\Models\Sp2dRekap::selectRaw('YEAR(tgl_sp2d) as year')
                                            ->whereNotNull('tgl_sp2d')
                                            ->distinct()
                                            ->orderBy('year', 'desc')
                                            ->pluck('year', 'year')
                                            ->toArray();
                                    })
                                    ->placeholder('Semua Tahun'),
                                \Filament\Forms\Components\TextInput::make('no_sp2d')
                                    ->label('Nomor SP2D')
                                    ->placeholder('Pencarian No. SP2D'),
                            ])
                    ])
                    ->columnSpan('full')
                    ->query(function (Builder $query, array $data): Builder {
                        // Filters are applied directly to the subquery to prevent "Column not found: sp2d_pajaks.sp2d_rekap_id"
                        return $query;
                    })
            ])
            ->filtersFormColumns(3)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('Lihat Rincian SP2D')
                    ->icon('heroicon-m-magnifying-glass')
                    ->modalHeading(fn ($record) => 'Rincian SP2D - ' . ($record->nama_pihak ?? 'Unknown'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('4xl')
                    ->modalContent(fn ($record, $livewire) => view('filament.pages.rincian-sp2d-modal', [
                        'pajaks' => $record ? Sp2dPajak::where('nama_pihak', $record->nama_pihak)
                            ->when(empty($record->npwp_nik), fn ($q) => $q->whereNull('npwp_nik'), fn ($q) => $q->where('npwp_nik', $record->npwp_nik))
                            ->whereHas('rekap', function ($q) use ($livewire) {
                                $q->where('status_verifikasi', 'valid');
                                $filterState = $livewire->getTableFilterState('periode') ?? [];
                                if (!empty($filterState['bulan'])) {
                                    $q->whereMonth('tgl_sp2d', $filterState['bulan']);
                                }
                                if (!empty($filterState['tahun'])) {
                                    $q->whereYear('tgl_sp2d', $filterState['tahun']);
                                }
                                if (!empty($filterState['no_sp2d'])) {
                                    $q->where('no_sp2d', 'like', "%{$filterState['no_sp2d']}%");
                                }
                            })
                            ->with('rekap')
                            ->get() : collect()
                    ])),
            ])
            ->defaultSort('nama_pihak')
            ->recordTitleAttribute('nama_pihak')
            ->striped();
    }

    private function getExportData($livewire): array
    {
        $akuns = \App\Models\AkunPajak::orderBy('kode')->get();
        $filterState = $livewire->getTableFilterState('periode') ?? [];
        $bulan = $filterState['bulan'] ?? null;
        $tahun = $filterState['tahun'] ?? null;
        $noSp2d = $filterState['no_sp2d'] ?? null;

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $monthsToExport = [];
        if ($bulan) {
            $monthsToExport[] = $bulan;
            $bulanName = $namaBulan[$bulan] ?? null;
        } else {
            $monthsToExport = array_keys($namaBulan);
            $bulanName = null;
        }

        $selectRaw = "
            MIN(id) as id,
            npwp_nik, 
            nama_pihak,
        ";
        foreach ($akuns as $akun) {
            $columnName = 'pajak_' . $akun->kode;
            $selectRaw .= "SUM(CASE WHEN kode_akun_pajak IN ('{$akun->kode}') THEN nominal_pajak ELSE 0 END) as {$columnName}, ";
        }
        $selectRaw .= "SUM(nominal_pajak) as total";

        $allMonthsData = [];
        $sheetsData = [];  // untuk multi-sheet Excel

        $headers = ['Nama Pihak', 'NPWP / NIK'];
        foreach ($akuns as $akun) {
            $headers[] = $akun->kode . ' - ' . $akun->nama_lengkap;
        }
        $headers[] = 'Total Potongan';

        foreach ($monthsToExport as $m) {
            $subquery = Sp2dPajak::query()
                ->selectRaw($selectRaw)
                ->whereHas('rekap', function ($q) use ($m, $tahun, $noSp2d) {
                    $q->where('status_verifikasi', 'valid');
                    $q->whereMonth('tgl_sp2d', $m);
                    if ($tahun) {
                        $q->whereYear('tgl_sp2d', $tahun);
                    }
                    if ($noSp2d) {
                        $q->where('no_sp2d', 'like', "%{$noSp2d}%");
                    }
                })
                ->groupBy('npwp_nik', 'nama_pihak');

            $query = Sp2dPajak::query()
                ->fromSub($subquery, 'sp2d_pajaks')
                ->orderBy('nama_pihak', 'asc');

            $records = $query->get();
            if ($records->isEmpty()) continue;

            $sums = array_fill_keys($akuns->pluck('kode')->toArray(), 0);
            $sumTotal = 0;
            $dataRows = [];

            foreach ($records as $record) {
                $row = [
                    $record->nama_pihak,
                    $record->npwp_nik,
                ];
                
                foreach ($akuns as $akun) {
                    $columnName = 'pajak_' . $akun->kode;
                    $val = $record->$columnName;
                    $row[] = $val ? number_format((float)$val, 0, ',', '.') : '-';
                    $sums[$akun->kode] += $val ?: 0;
                }
                
                $row[] = $record->total ? number_format((float)$record->total, 0, ',', '.') : '-';
                $sumTotal += $record->total ?: 0;
                
                $dataRows[] = $row;
            }

            $grandTotalRow = ['GRAND TOTAL', ''];
            foreach ($akuns as $akun) {
                $val = $sums[$akun->kode];
                $grandTotalRow[] = $val ? number_format((float)$val, 0, ',', '.') : '-';
            }
            $grandTotalRow[] = $sumTotal ? number_format((float)$sumTotal, 0, ',', '.') : '-';

            // Nama sheet format: {tahun}_{bulan}_{namaBulan}
            $tahunLabel = $tahun ?? date('Y');
            $sheetTitle = $tahunLabel . '_' . $m . '_' . $namaBulan[$m];

            // Rows untuk sheet: header + data + grand total
            $sheetRows = array_merge([$headers], $dataRows, [$grandTotalRow]);

            $allMonthsData[] = [
                'bulanName' => $namaBulan[$m],
                'headers'   => $headers,
                'rows'      => $dataRows,
                'grandTotal' => $grandTotalRow,
            ];

            $sheetsData[] = [
                'sheetTitle' => $sheetTitle,
                'rows'       => $sheetRows,
            ];
        }

        if (empty($allMonthsData)) {
            $tahunLabel = $tahun ?? date('Y');
            $mLabel = $bulan ?? '00';
            $sheetTitle = $tahunLabel . '_' . $mLabel . '_' . ($namaBulan[$bulan] ?? 'Data');

            $allMonthsData[] = [
                'bulanName'  => $bulanName,
                'headers'    => $headers,
                'rows'       => [],
                'grandTotal' => array_fill(0, count($headers), '-'),
            ];
            $sheetsData[] = [
                'sheetTitle' => $sheetTitle,
                'rows'       => [$headers],
            ];
        }

        $nameParts = ['Rekap_Pajak'];
        if ($bulanName) $nameParts[] = $bulanName;
        if ($tahun) $nameParts[] = $tahun;
        if ($noSp2d) $nameParts[] = 'SP2D_' . preg_replace('/[^a-zA-Z0-9]/', '', $noSp2d);
        $nameParts[] = date('d-M-Y_H-i');
        $filename = implode('_', $nameParts);
        
        return [
            'filename' => $filename,
            'months'   => $allMonthsData,
            'sheets'   => $sheetsData,
            'bulan'    => $bulanName,
            'tahun'    => $tahun,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function ($livewire) {
                        $exportInfo = $this->getExportData($livewire);
                        
                        // Build flat CSV dari semua sheets
                        $finalCsvData = [];
                        foreach ($exportInfo['sheets'] as $sheet) {
                            if (count($exportInfo['sheets']) > 1) {
                                $finalCsvData[] = ['Bulan: ' . $sheet['sheetTitle']];
                            }
                            foreach ($sheet['rows'] as $row) {
                                $finalCsvData[] = $row;
                            }
                            $finalCsvData[] = [];
                        }
                        if (!empty($finalCsvData)) array_pop($finalCsvData);

                        $filename = $exportInfo['filename'] . '.csv';
                        $path = public_path('exports');
                        if (!file_exists($path)) mkdir($path, 0777, true);
                        
                        $file = fopen($path . '/' . $filename, 'w');
                        fputs($file, "\xEF\xBB\xBF");
                        foreach ($finalCsvData as $row) {
                            fputcsv($file, $row, ';');
                        }
                        fclose($file);
                        
                        $url = route('exports.download', ['filename' => $filename]);
                        $this->js("window.open('{$url}', '_blank');");
                    }),
                \Filament\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-chart-bar')
                    ->action(function ($livewire) {
                        $exportInfo = $this->getExportData($livewire);
                        
                        $filename = $exportInfo['filename'] . '.xlsx';
                        $path = public_path('exports');
                        if (!file_exists($path)) mkdir($path, 0777, true);
                        \Maatwebsite\Excel\Facades\Excel::store(
                            new \App\Exports\RekapPerPihakExport($exportInfo['sheets']),
                            'exports/' . $filename,
                            'real_public',
                            \Maatwebsite\Excel\Excel::XLSX
                        );
                        
                        $url = route('exports.download', ['filename' => $filename]);
                        $this->js("window.open('{$url}', '_blank');");
                    }),
                \Filament\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->action(function ($livewire) {
                        $exportInfo = $this->getExportData($livewire);
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.rekap-per-pihak', [
                            'months' => $exportInfo['months'],
                            'filterBulan' => $exportInfo['bulan'],
                            'filterTahun' => $exportInfo['tahun'],
                        ])->setPaper('a4', 'landscape');
                        
                        $filename = $exportInfo['filename'] . '.pdf';
                        $path = public_path('exports');
                        if (!file_exists($path)) mkdir($path, 0777, true);
                        file_put_contents($path . '/' . $filename, $pdf->output());
                        
                        $url = route('exports.download', ['filename' => $filename]);
                        $this->js("window.open('{$url}', '_blank');");
                    }),
            ])
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->button(),
        ];
    }
}
