<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp2dRekapResource\Pages;
use App\Models\Sp2dRekap;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
class Sp2dRekapResource extends Resource
{
    protected static ?string $model = Sp2dRekap::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string|\UnitEnum|null $navigationGroup = 'Rekap SP2D';
    protected static ?string $label = 'Data Rekap SP2D';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make(3)
                    ->columnSpan('full')
                    ->schema([
                    \Filament\Schemas\Components\Section::make('Rincian SP2D')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\TextInput::make('no_sp2d')
                                ->label('No. SP2D')
                                ->disabled(),
                            Forms\Components\DatePicker::make('tgl_sp2d')
                                ->label('Tgl. SP2D')
                                ->disabled(),
                            Forms\Components\TextInput::make('jenis_spm')
                                ->label('Jenis SPM')
                                ->disabled(),
                            Forms\Components\TextInput::make('jalur_transaksi')
                                ->label('Jalur Transaksi')
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    '1_pihak' => '1 Pihak',
                                    'banyak_pihak' => 'Banyak Pihak',
                                    'up' => 'UP',
                                    default => (string)$state,
                                })
                                ->disabled(),
                            Forms\Components\Textarea::make('uraian')
                                ->label('Uraian SPM')
                                ->disabled()
                                ->rows(2),
                            Forms\Components\TextInput::make('jumlah_pengeluaran')
                                ->label('Bruto (Pengeluaran)')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                ->stripCharacters('.')
                                ->numeric()
                                ->disabled(),
                            Forms\Components\TextInput::make('jumlah_potongan')
                                ->label('Total Potongan')
                                ->helperText('Target akumulasi potongan pajak yang harus dipenuhi oleh rincian.')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                ->stripCharacters('.')
                                ->numeric()
                                ->disabled(),
                            Forms\Components\TextInput::make('jumlah_pembayaran')
                                ->label('Netto (Pembayaran)')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                ->stripCharacters('.')
                                ->numeric()
                                ->disabled(),
                            Forms\Components\TextInput::make('atas_nama_default')
                                ->label('Atas Nama Default')
                                ->helperText('Identitas penerima default dari berkas Potongan SPM.'),
                            Forms\Components\Select::make('status_verifikasi')
                                ->label('Status Verifikasi')
                                ->helperText('Otomatis "Valid" jika total rincian = target potongan. (Khusus jalur UP dapat diubah manual)')
                                ->options([
                                    'perlu_rincian' => 'Perlu Rincian',
                                    'valid' => 'Valid',
                                    ])
                                ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== 'up')
                                ->dehydrated()
                                ->required(),
                        ])->columns(1),

                    \Filament\Schemas\Components\Section::make('Rincian Pajak')
                        ->columnSpan(2)
                        ->description('Untuk SP2D Jalur Banyak Pihak, pastikan Total Pajak sama dengan Target Potongan.')
                        ->schema([
                            \Filament\Schemas\Components\Actions::make([
                                \Filament\Actions\Action::make('upload_rincian_excel')
                                    ->label('Upload Rincian via Excel')
                                    ->icon('heroicon-o-arrow-up-tray')
                                    ->color('success')
                                    ->tooltip('Ekstrak otomatis rincian potongan pajak dari Excel Gaji/Tukin/Uang Makan/Lembur')
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak')
                                    ->form([
                                        Forms\Components\Select::make('jenis_file')
                                            ->label('Jenis File Excel')
                                            ->options([
                                                'gaji' => 'Daftar Gaji Pusat',
                                                'tukin' => 'Daftar Tukin',
                                                'uang_makan' => 'Uang Makan',
                                                'uang_lembur' => 'Uang Lembur',
                                            ])
                                            ->required(),
                                        Forms\Components\FileUpload::make('file_excel')
                                            ->label('File Excel')
                                            ->storeFiles(false)
                                            ->required()
                                    ])
                                    ->action(function (array $data, $set) {
                                        if (empty($data['file_excel'])) {
                                            return;
                                        }
                                        $file = $data['file_excel'];
                                        try {
                                            $results = \App\Services\RincianImportService::parseExcelData($file->getRealPath(), $data['jenis_file']);
                                            
                                            $grouped = [];
                                            foreach ($results as $pajak) {
                                                $key = $pajak['npwp_nik'] . '|' . $pajak['nama_pihak'];
                                                if (!isset($grouped[$key])) {
                                                    $grouped[$key] = [
                                                        'npwp_nik' => $pajak['npwp_nik'],
                                                        'nama_pihak' => $pajak['nama_pihak'],
                                                        'rincian_pajak' => []
                                                    ];
                                                }
                                                $grouped[$key]['rincian_pajak'][] = [
                                                    'kode_akun_pajak' => $pajak['kode_akun_pajak'],
                                                    'dpp' => $pajak['dpp'],
                                                    'nominal_pajak' => $pajak['nominal_pajak'],
                                                    '_is_selected' => false,
                                                ];
                                            }
                                            
                                            // Timpa form grouped_pajaks dengan hasil ekstrak
                                            $set('grouped_pajaks', array_values($grouped));
                                            
                                            \Filament\Notifications\Notification::make()
                                                ->success()
                                                ->title('Berhasil Membaca Excel')
                                                ->body('Total ' . count($results) . ' rincian pajak berhasil diekstrak. Periksa hasilnya di bawah, lalu klik Save changes.')
                                                ->send();
                                        } catch (\Exception $e) {
                                            \Filament\Notifications\Notification::make()
                                                ->danger()
                                                ->title('Gagal Membaca Excel')
                                                ->body($e->getMessage())
                                                ->send();
                                        }
                                    })
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak'),
                                    
                                \Filament\Actions\Action::make('hapus_semua')
                                    ->label('Hapus Semua')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->tooltip('Hapus seluruh rincian pajak pada SP2D ini sekaligus')
                                    ->requiresConfirmation()
                                    ->action(function ($set) {
                                        $set('grouped_pajaks', []);
                                    })
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak'),

                                \Filament\Actions\Action::make('hapus_terpilih')
                                    ->label('Hapus Terpilih')
                                    ->icon('heroicon-o-backspace')
                                    ->color('warning')
                                    ->tooltip('Hapus baris-baris rincian pajak yang sedang dicentang')

                                    ->action(function ($set, $get) {
                                        $grouped = $get('grouped_pajaks') ?? [];
                                        $newGrouped = [];
                                        foreach ($grouped as $group) {
                                            $newRincian = array_filter($group['rincian_pajak'] ?? [], fn($item) => empty($item['_is_selected']));
                                            if (count($newRincian) > 0) {
                                                $group['rincian_pajak'] = array_values($newRincian);
                                                $newGrouped[] = $group;
                                            }
                                        }
                                        $set('grouped_pajaks', $newGrouped);
                                    })
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak'),
                            ])->alignEnd(),

                            Forms\Components\Placeholder::make('total_rincian_saat_ini_top')
                                ->label('Total Rincian Saat Ini')
                                ->content(fn (\Filament\Schemas\Components\Utilities\Get $get) => static::getTotalRincianSaatIniContent($get)),

                            Forms\Components\Repeater::make('grouped_pajaks')
                                ->label('Daftar Pihak/Penerima')
                                ->collapsible()
                                ->cloneable(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak')
                                ->addable(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak')
                                ->deletable(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('jalur_transaksi') !== '1_pihak')
                                ->itemLabel(fn (array $state): ?string => $state['nama_pihak'] ?? null)
                                ->schema([
                                    \Filament\Schemas\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('search_pihak')
                                                ->label('Cari Pegawai / Pihak')
                                                ->placeholder('Ketik Nama atau NIP...')
                                                ->searchable()
                                                ->dehydrated(false)
                                                ->getOptionLabelUsing(fn ($value): ?string => is_string($value) && json_decode($value, true) ? json_decode($value, true)['nama'] : $value)
                                                ->getSearchResultsUsing(function (string $search) {
                                                    $users = \App\Models\User::where('name', 'like', "%{$search}%")
                                                        ->orWhere('nip_baru', 'like', "%{$search}%")
                                                        ->orWhere('nip', 'like', "%{$search}%")
                                                        ->limit(10)
                                                        ->get()
                                                        ->mapWithKeys(fn ($user) => [
                                                            json_encode(['nama' => $user->name, 'npwp' => $user->nip_baru ?? $user->nip]) => "{$user->name} (" . ($user->nip_baru ?? $user->nip ?? '-') . ")"
                                                        ]);
                                                    
                                                    $pajaks = \App\Models\Sp2dPajak::where('nama_pihak', 'like', "%{$search}%")
                                                        ->orWhere('npwp_nik', 'like', "%{$search}%")
                                                        ->select('nama_pihak', 'npwp_nik')
                                                        ->distinct()
                                                        ->limit(10)
                                                        ->get()
                                                        ->mapWithKeys(fn ($p) => [
                                                            json_encode(['nama' => $p->nama_pihak, 'npwp' => $p->npwp_nik]) => "{$p->nama_pihak} (" . ($p->npwp_nik ?? '-') . ")"
                                                        ]);
                                                        
                                                    return $users->union($pajaks)->toArray();
                                                })
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set) {
                                                    if ($state) {
                                                        $data = json_decode($state, true);
                                                        if (is_array($data)) {
                                                            $set('nama_pihak', $data['nama'] ?? '');
                                                            $set('npwp_nik', $data['npwp'] ?? '');
                                                        }
                                                        $set('search_pihak', null);
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('npwp_nik')
                                                ->label('NPWP / NIK'),
                                            Forms\Components\TextInput::make('nama_pihak')
                                                ->label('Nama Pihak')
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Nama pihak wajib diisi.',
                                                ]),
                                        ]),
                                    Forms\Components\Repeater::make('rincian_pajak')
                                        ->label('Rincian Pajak')
                                        ->addActionLabel('Tambah Rincian')
                                        ->cloneable(fn (\Filament\Schemas\Components\Utilities\Get $get) => ($get('../../jalur_transaksi') ?? $get('jalur_transaksi')) !== '1_pihak')
                                        ->addable(fn (\Filament\Schemas\Components\Utilities\Get $get) => ($get('../../jalur_transaksi') ?? $get('jalur_transaksi')) !== '1_pihak')
                                        ->deletable(fn (\Filament\Schemas\Components\Utilities\Get $get) => ($get('../../jalur_transaksi') ?? $get('jalur_transaksi')) !== '1_pihak')
                                        ->schema([
                                            Forms\Components\Select::make('kode_akun_pajak')
                                                ->label('Jenis Pajak')
                                                ->options(fn () => static::getAkunPajakOptions())
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Jenis pajak wajib dipilih.',
                                                ]),
                                            Forms\Components\TextInput::make('dpp')
                                                ->label('DPP')
                                                ->prefix('Rp')
                                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                                ->stripCharacters('.')
                                                ->numeric()
                                                ->default(0),
                                            Forms\Components\TextInput::make('nominal_pajak')
                                                ->label('Nominal Pajak')
                                                ->prefix('Rp')
                                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                                ->stripCharacters('.')
                                                ->numeric()
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Nominal pajak wajib diisi.',
                                                ])
                                                ->live(onBlur: true),
                                            Forms\Components\Checkbox::make('_is_selected')
                                                ->label('Pilih')
                                                ->dehydrated(false)
                                                ->inline(false)
                                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => ($get('../../jalur_transaksi') ?? $get('jalur_transaksi')) !== '1_pihak'),
                                        ])
                                        ->columns(4)
                                        ->defaultItems(1)
                                 ])
                                 ->columns(1)
                                 ->defaultItems(0)
                                 ->addActionLabel('Tambah Pihak/Penerima'),

                            Forms\Components\Placeholder::make('total_rincian_saat_ini')
                                ->label('Total Rincian Saat Ini')
                                ->content(fn (\Filament\Schemas\Components\Utilities\Get $get) => static::getTotalRincianSaatIniContent($get)),
                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'scroll-top-table'])
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with('pajaks'))
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('tgl_sp2d')
                    ->label('Tgl SP2D')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jenis_spm')
                    ->label('Jenis SPM')
                    ->limit(10)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen((string)$state) > 10 ? $state : null;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('uraian')
                    ->label('Uraian')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen((string)$state) > 30 ? $state : null;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('jalur_transaksi')
                    ->label('Jalur')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        '1_pihak' => '1 Pihak',
                        'banyak_pihak' => 'Banyak Pihak',
                        'up' => 'UP',
                        default => (string)$state,
                    })
                    ->colors([
                        'success' => '1_pihak',
                        'warning' => 'banyak_pihak',
                        'danger' => 'up',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nama_pihak')
                    ->label('Nama Pihak')
                    ->state(function (Sp2dRekap $record) {
                        $pihak = $record->pajaks->pluck('nama_pihak')->unique()->filter();
                        $count = $pihak->count();
                        if ($count === 0) return '-';
                        if ($count === 1) return $pihak->first();
                        return 'Banyak Pihak';
                    })
                    ->limit(25)
                    ->tooltip(function (Sp2dRekap $record, Tables\Columns\TextColumn $column) {
                        $pihak = $record->pajaks->pluck('nama_pihak')->unique()->filter();
                        if ($pihak->count() > 1) {
                            return $pihak->implode(', ');
                        }
                        
                        $state = $column->getState();
                        return strlen((string)$state) > 25 ? $state : null;
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('pajaks', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                            $query->where('nama_pihak', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jumlah_pengeluaran')
                    ->label('Bruto')
                    ->money('IDR', locale: 'id')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jumlah_potongan')
                    ->label('Potongan / Pajak')
                    ->money('IDR', locale: 'id')
                    ->description(function (Sp2dRekap $record) {
                        $pajak = $record->total_pajak;
                        return abs($record->jumlah_potongan - $pajak) < 0.1 
                            ? 'Pajak: Rp ' . number_format($pajak, 0, ',', '.') . ' (Sesuai)'
                            : 'Pajak: Rp ' . number_format($pajak, 0, ',', '.') . ' (Selisih)';
                    })
                    ->color(function (Sp2dRekap $record) {
                        return abs($record->jumlah_potongan - $record->total_pajak) < 0.1 ? 'success' : 'danger';
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'valid',
                        'warning' => 'perlu_rincian',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_sp2d')
                    ->label('No SP2D')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('No SP2D berhasil disalin')
                    ->copyMessageDuration(1500),
            ])
            ->filters([
                Tables\Filters\Filter::make('filters')
                    ->form([
                        \Filament\Schemas\Components\Grid::make(5)
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
                                        return Sp2dRekap::selectRaw('YEAR(tgl_sp2d) as year')
                                            ->whereNotNull('tgl_sp2d')
                                            ->distinct()
                                            ->orderBy('year', 'desc')
                                            ->pluck('year', 'year')
                                            ->toArray();
                                    })
                                    ->placeholder('Semua Tahun'),
                                \Filament\Forms\Components\Select::make('jenis_spm')
                                    ->label('Jenis SPM')
                                    ->options(function () {
                                        return Sp2dRekap::select('jenis_spm')
                                            ->distinct()
                                            ->whereNotNull('jenis_spm')
                                            ->where('jenis_spm', '!=', '')
                                            ->pluck('jenis_spm', 'jenis_spm')
                                            ->toArray();
                                    })
                                    ->placeholder('Semua Jenis'),
                                \Filament\Forms\Components\Select::make('jalur_transaksi')
                                    ->label('Jalur Transaksi')
                                    ->options([
                                        '1_pihak' => '1 Pihak',
                                        'banyak_pihak' => 'Banyak Pihak',
                                        'up' => 'UP',
                                    ])
                                    ->placeholder('Semua Jalur'),
                                \Filament\Forms\Components\Select::make('status_verifikasi')
                                    ->label('Status Verifikasi')
                                    ->options([
                                        'valid' => 'Valid',
                                        'perlu_rincian' => 'Perlu Rincian',
                                    ])
                                    ->placeholder('Semua Status'),
                            ])
                    ])
                    ->columnSpan('full')
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(!empty($data['bulan']), fn ($q) => $q->whereMonth('tgl_sp2d', $data['bulan']))
                            ->when(!empty($data['tahun']), fn ($q) => $q->whereYear('tgl_sp2d', $data['tahun']))
                            ->when(!empty($data['jenis_spm']), fn ($q) => $q->where('jenis_spm', $data['jenis_spm']))
                            ->when(!empty($data['jalur_transaksi']), fn ($q) => $q->where('jalur_transaksi', $data['jalur_transaksi']))
                            ->when(!empty($data['status_verifikasi']), fn ($q) => $q->where('status_verifikasi', $data['status_verifikasi']));
                    }),
            ])
            ->filtersFormColumns(5)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordActionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->recordActions([
                \Filament\Actions\Action::make('upload_rincian_excel_row')
                    ->label('Upload Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->tooltip('Ekstrak otomatis rincian potongan pajak dari Excel Gaji/Tukin/Uang Makan/Lembur')
                    ->visible(fn (Sp2dRekap $record) => $record->jalur_transaksi !== '1_pihak')
                    ->form([
                        Forms\Components\Select::make('jenis_file')
                            ->label('Jenis File Excel')
                            ->options([
                                'gaji' => 'Daftar Gaji Pusat',
                                'tukin' => 'Daftar Tukin',
                                'uang_makan' => 'Uang Makan',
                                'uang_lembur' => 'Uang Lembur',
                            ])
                            ->required(),
                        Forms\Components\FileUpload::make('file_excel')
                            ->label('File Excel')
                            ->storeFiles(false)
                            ->required(),
                    ])
                    ->action(function (array $data, Sp2dRekap $record) {
                        if (empty($data['file_excel'])) {
                            return;
                        }
                        $file = $data['file_excel'];
                        try {
                            $results = \App\Services\RincianImportService::parseExcelData($file->getRealPath(), $data['jenis_file']);
                            
                            $flat = [];
                            foreach ($results as $pajak) {
                                $flat[] = [
                                    'npwp_nik' => $pajak['npwp_nik'] ?? null,
                                    'nama_pihak' => $pajak['nama_pihak'],
                                    'kode_akun_pajak' => $pajak['kode_akun_pajak'],
                                    'dpp' => (float)($pajak['dpp'] ?? 0),
                                    'nominal_pajak' => (float)($pajak['nominal_pajak'] ?? 0),
                                ];
                            }
                            
                            $record->pajaks()->delete();
                            $record->pajaks()->createMany($flat);
                            
                            if ($record->jalur_transaksi !== 'up') {
                                $totalPajak = $record->pajaks()->sum('nominal_pajak');
                                if (abs($totalPajak - $record->jumlah_potongan) < 0.1) {
                                    $record->update(['status_verifikasi' => 'valid']);
                                } else {
                                    $record->update(['status_verifikasi' => 'perlu_rincian']);
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Berhasil Impor Rincian Pajak')
                                ->body('Total ' . count($results) . ' rincian pajak berhasil disimpan ke SP2D No. ' . $record->no_sp2d)
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Gagal Membaca Excel')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                \Filament\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->tooltip('Buka form edit untuk melihat & mengelola rincian penerima serta potongan pajak')
                    ->record(function (\Filament\Actions\EditAction $action) {

                        static $resolvedRecords = [];
                        $key = $action->getTable()->getMountedActionRecordKey();
                        if (!$key) return null;
                        return $resolvedRecords[$key] ??= $action->getTable()->getRecord($key);
                    })
                    ->mutateRecordDataUsing(function (array $data, Sp2dRekap $record): array {
                        $pajaks = $record->pajaks->toArray();
                        $grouped = [];
                        foreach ($pajaks as $pajak) {
                            $key = $pajak['npwp_nik'] . '|' . $pajak['nama_pihak'];
                            if (!isset($grouped[$key])) {
                                $grouped[$key] = [
                                    'npwp_nik' => $pajak['npwp_nik'],
                                    'nama_pihak' => $pajak['nama_pihak'],
                                    'rincian_pajak' => []
                                ];
                            }
                            $grouped[$key]['rincian_pajak'][] = [
                                'kode_akun_pajak' => $pajak['kode_akun_pajak'],
                                'dpp' => $pajak['dpp'],
                                'nominal_pajak' => $pajak['nominal_pajak'],
                                '_is_selected' => false,
                            ];
                        }
                        $data['grouped_pajaks'] = array_values($grouped);
                        return $data;
                    })
                    ->using(function (Sp2dRekap $record, array $data): \Illuminate\Database\Eloquent\Model {
                        $record->update($data);
                        
                        $flat = [];
                        foreach ($data['grouped_pajaks'] ?? [] as $group) {
                            foreach ($group['rincian_pajak'] ?? [] as $rincian) {
                                $flat[] = [
                                    'npwp_nik' => $group['npwp_nik'] ?? null,
                                    'nama_pihak' => $group['nama_pihak'],
                                    'kode_akun_pajak' => $rincian['kode_akun_pajak'],
                                    'dpp' => (float) preg_replace('/[^0-9\-]/', '', (string)($rincian['dpp'] ?? '0')),
                                    'nominal_pajak' => (float) preg_replace('/[^0-9\-]/', '', (string)($rincian['nominal_pajak'] ?? '0')),
                                ];
                            }
                        }
                        
                        $record->pajaks()->delete();
                        $record->pajaks()->createMany($flat);
                        
                        return $record;
                    })
                    ->after(function (Sp2dRekap $record) {
                        if ($record->jalur_transaksi !== 'up') {
                            $totalPajak = $record->pajaks()->sum('nominal_pajak');
                            
                            if (abs($totalPajak - $record->jumlah_potongan) < 0.1) {
                                if ($record->status_verifikasi !== 'valid') {
                                    $record->update(['status_verifikasi' => 'valid']);
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('Status Diperbarui')
                                        ->body('Total rincian pajak telah sesuai dengan target potongan. Status verifikasi otomatis diubah menjadi Valid.')
                                        ->send();
                                }
                            } else {
                                if ($record->status_verifikasi === 'valid') {
                                    $record->update(['status_verifikasi' => 'perlu_rincian']);
                                }
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Peringatan')
                                    ->body('Total rincian pajak harus seimbang dengan target potongan.')
                                    ->send();
                            }
                        }
                    }),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getAkunPajakOptions(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'akun_pajak_options_map',
            3600,
            fn () => \App\Models\AkunPajak::orderBy('kode')
                ->get()
                ->mapWithKeys(fn ($item) => [$item->kode => $item->kode . ' - ' . $item->nama_pendek])
                ->toArray()
        );
    }

    public static function getTotalRincianSaatIniContent(\Filament\Schemas\Components\Utilities\Get $get, ?Sp2dRekap $record = null): \Illuminate\Support\HtmlString
    {
        $grouped = $get('grouped_pajaks') ?? [];
        $total = 0;
        foreach ($grouped as $group) {
            foreach ($group['rincian_pajak'] ?? [] as $item) {
                $total += (float)preg_replace('/[^0-9\-]/', '', (string)($item['nominal_pajak'] ?? '0'));
            }
        }

        $text = "Rp" . number_format($total, 0, ',', '.');

        $jalurTransaksi = $get('jalur_transaksi') ?? $record?->jalur_transaksi;
        if ($jalurTransaksi === 'up') {
            return new \Illuminate\Support\HtmlString("<span style='font-weight: bold;'>{$text} (UP: Tidak Terikat Target)</span>");
        }

        $target = (float)preg_replace('/[^0-9\-]/', '', (string)($get('jumlah_potongan') ?? '0'));
        $sisa = $target - $total;

        if (abs($sisa) < 0.1) {
            return new \Illuminate\Support\HtmlString("<span style='color: green; font-weight: bold;'>{$text} (Sesuai)</span>");
        } elseif ($sisa > 0) {
            return new \Illuminate\Support\HtmlString("<span style='color: red; font-weight: bold;'>{$text} (Kurang Rp" . number_format($sisa, 0, ',', '.') . ")</span>");
        } else {
            return new \Illuminate\Support\HtmlString("<span style='color: red; font-weight: bold;'>{$text} (Lebih Rp" . number_format(abs($sisa), 0, ',', '.') . ")</span>");
        }
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp2dRekaps::route('/'),
        ];
    }
}
