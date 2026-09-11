<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp2dRekapResource\Pages;
use App\Models\Sp2dRekap;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
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
                                ->label('Atas Nama Default'),
                            Forms\Components\Select::make('status_verifikasi')
                                ->label('Status Verifikasi')
                                ->options([
                                    'perlu_rincian' => 'Perlu Rincian',
                                    'valid' => 'Valid',
                                ])
                                ->disabled(fn (?Sp2dRekap $record) => $record?->jalur_transaksi !== 'gup')
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
                                    ->visible(fn (?Sp2dRekap $record) => $record && $record->jalur_transaksi !== '1_pihak')
                                    ->form([
                                        Forms\Components\Select::make('jenis_file')
                                            ->label('Jenis File Excel')
                                            ->options([
                                                'gaji' => 'Daftar Gaji Pusat',
                                                'tukin' => 'Daftar Tukin',
                                                'uang_makan' => 'Uang Makan',
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
                                    ->visible(fn (?Sp2dRekap $record) => $record && $record->jalur_transaksi !== '1_pihak'),
                                    
                                \Filament\Actions\Action::make('hapus_semua')
                                    ->label('Hapus Semua')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->requiresConfirmation()
                                    ->action(function ($set) {
                                        $set('grouped_pajaks', []);
                                    })
                                    ->visible(fn (?Sp2dRekap $record) => $record && $record->jalur_transaksi !== '1_pihak'),

                                \Filament\Actions\Action::make('hapus_terpilih')
                                    ->label('Hapus Terpilih')
                                    ->icon('heroicon-o-backspace')
                                    ->color('warning')
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
                                    ->visible(fn (?Sp2dRekap $record) => $record && $record->jalur_transaksi !== '1_pihak'),
                            ])->alignEnd(),

                            Forms\Components\Repeater::make('grouped_pajaks')
                                ->label('Daftar Pihak/Penerima')
                                ->collapsible()
                                ->cloneable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                ->addable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                ->deletable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                ->itemLabel(fn (array $state): ?string => $state['nama_pihak'] ?? null)
                                ->schema([
                                    \Filament\Schemas\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('search_pihak')
                                                ->label('Cari Pegawai / Pihak')
                                                ->placeholder('Ketik Nama atau NIP...')
                                                ->searchable()
                                                ->dehydrated(false)
                                                ->getSearchResultsUsing(function (string $search) {
                                                    $users = \App\Models\User::where('name', 'like', "%{$search}%")
                                                        ->orWhere('nip_baru', 'like', "%{$search}%")
                                                        ->orWhere('nip_lama', 'like', "%{$search}%")
                                                        ->limit(10)
                                                        ->get()
                                                        ->mapWithKeys(fn ($user) => [
                                                            json_encode(['nama' => $user->name, 'npwp' => $user->nip_baru ?? $user->nip_lama]) => "{$user->name} (" . ($user->nip_baru ?? $user->nip_lama ?? '-') . ")"
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
                                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
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
                                        ->cloneable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                        ->addable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                        ->deletable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                        ->schema([
                                            Forms\Components\Select::make('kode_akun_pajak')
                                                ->label('Jenis Pajak')
                                                ->options(\App\Models\AkunPajak::all()->mapWithKeys(fn($item) => [$item->kode => $item->kode . ' - ' . $item->nama_pendek])->toArray())
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
                                                ->visible(fn ($record) => $record?->jalur_transaksi !== '1_pihak'),
                                        ])
                                        ->columns(4)
                                        ->defaultItems(1)
                                ])
                                ->columns(1)
                                ->defaultItems(0)
                                ->addActionLabel('Tambah Pihak/Penerima'),

                            Forms\Components\Placeholder::make('total_rincian_saat_ini')
                                ->label('Total Rincian Saat Ini')
                                ->content(function (\Filament\Schemas\Components\Utilities\Get $get, ?Sp2dRekap $record) {
                                    $grouped = $get('grouped_pajaks') ?? [];
                                    $total = 0;
                                    foreach ($grouped as $group) {
                                        foreach ($group['rincian_pajak'] ?? [] as $item) {
                                            $total += (float)preg_replace('/[^0-9\-]/', '', (string)($item['nominal_pajak'] ?? '0'));
                                        }
                                    }
                                    
                                    $text = "Rp" . number_format($total, 0, ',', '.');
                                    
                                    if ($record && $record->jalur_transaksi === 'gup') {
                                        return new \Illuminate\Support\HtmlString("<span style='font-weight: bold;'>{$text} (GUP: Tidak Terikat Target)</span>");
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
                                }),
                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'scroll-top-table'])
            ->poll('5s')
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('no_sp2d')
                    ->label('No SP2D')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('No SP2D berhasil disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('tgl_sp2d')
                    ->label('Tgl SP2D')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jenis_spm')
                    ->label('Jenis SPM')
                    ->limit(25)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen((string)$state) > 25 ? $state : null;
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
                    ->colors([
                        'success' => '1_pihak',
                        'warning' => 'banyak_pihak',
                        'danger' => 'gup',
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
                    ->label('Potongan')
                    ->money('IDR', locale: 'id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pajak')
                    ->label('Pajak')
                    ->money('IDR', locale: 'id')
                    ->state(function (Sp2dRekap $record) {
                        return $record->total_pajak;
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->orderBy(
                            \App\Models\Sp2dPajak::selectRaw('COALESCE(SUM(nominal_pajak), 0)')
                                ->whereColumn('sp2d_rekaps.id', 'sp2d_pajaks.sp2d_rekap_id'),
                            $direction
                        );
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'valid',
                        'warning' => 'perlu_rincian',
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis_spm')
                    ->label('Jenis SPM')
                    ->options(function () {
                        return Sp2dRekap::query()
                            ->select('jenis_spm')
                            ->distinct()
                            ->whereNotNull('jenis_spm')
                            ->where('jenis_spm', '!=', '')
                            ->pluck('jenis_spm', 'jenis_spm')
                            ->toArray();
                    }),
                SelectFilter::make('jalur_transaksi')
                    ->label('Jalur')
                    ->options([
                        '1_pihak' => '1 Pihak',
                        'banyak_pihak' => 'Banyak Pihak',
                        'gup' => 'GUP',
                    ]),
                SelectFilter::make('status_verifikasi')
                    ->options([
                        'valid' => 'Valid',
                        'perlu_rincian' => 'Perlu Rincian',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
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
                        if ($record->jalur_transaksi !== 'gup') {
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
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Status Diperbarui')
                                        ->body('Total rincian pajak belum sesuai dengan target potongan. Status dikembalikan menjadi Perlu Rincian.')
                                        ->send();
                                }
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
