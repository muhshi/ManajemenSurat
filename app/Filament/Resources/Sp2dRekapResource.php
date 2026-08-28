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
                            Forms\Components\Repeater::make('pajaks')
                                ->relationship()
                                ->label('Daftar Pajak Pihak/Penerima')
                                ->collapsible()
                                ->addable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                ->deletable(fn ($record) => $record?->jalur_transaksi !== '1_pihak')
                                ->itemLabel(fn (array $state): ?string => $state['nama_pihak'] ?? null)
                                ->schema([
                                    \Filament\Schemas\Components\Grid::make(5)
                                        ->schema([
                                            Forms\Components\TextInput::make('npwp_nik')
                                                ->label('NPWP / NIK')
                                                ->columnSpan(2),
                                            Forms\Components\TextInput::make('nama_pihak')
                                                ->label('Nama Pihak')
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Nama pihak wajib diisi.',
                                                ])
                                                ->columnSpan(3),
                                        ]),
                                    \Filament\Schemas\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('kode_akun_pajak')
                                                ->label('Jenis/Akun Pajak')
                                                ->options([
                                                    '411121' => '411121 - PPh 21',
                                                    '411122' => '411122 - PPh 22',
                                                    '411124' => '411124 - PPh 23',
                                                    '411211' => '411211 - PPN',
                                                    '411128' => '411128 - PPh Final',
                                                ])
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Jenis pajak wajib dipilih.',
                                                ])
                                                ->columnSpan(1),
                                            Forms\Components\TextInput::make('dpp')
                                                ->label('DPP')
                                                ->prefix('Rp')
                                                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                                ->stripCharacters('.')
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
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
                                                ->live(onBlur: true)
                                                ->columnSpan(1),
                                        ])
                                ])
                                ->columns(1)
                                ->defaultItems(0)
                                ->addActionLabel('Tambah Rincian Pajak'),

                            Forms\Components\Placeholder::make('total_rincian_saat_ini')
                                ->label('Total Rincian Saat Ini')
                                ->content(function (\Filament\Schemas\Components\Utilities\Get $get, ?Sp2dRekap $record) {
                                    $pajaks = $get('pajaks') ?? [];
                                    $total = collect($pajaks)->sum(function ($item) {
                                        return (float)preg_replace('/[^0-9\-]/', '', (string)($item['nominal_pajak'] ?? '0'));
                                    });
                                    
                                    $text = "Rp " . number_format($total, 0, ',', '.');
                                    
                                    if ($record && $record->jalur_transaksi === 'gup') {
                                        return new \Illuminate\Support\HtmlString("<span style='font-weight: bold;'>{$text} (GUP: Tidak Terikat Target)</span>");
                                    }
                                    
                                    $target = (float)preg_replace('/[^0-9\-]/', '', (string)($get('jumlah_potongan') ?? '0'));
                                    $sisa = $target - $total;
                                    
                                    if (abs($sisa) < 0.1) {
                                        return new \Illuminate\Support\HtmlString("<span style='color: green; font-weight: bold;'>{$text} (Sesuai)</span>");
                                    } elseif ($sisa > 0) {
                                        return new \Illuminate\Support\HtmlString("<span style='color: red; font-weight: bold;'>{$text} (Kurang Rp " . number_format($sisa, 0, ',', '.') . ")</span>");
                                    } else {
                                        return new \Illuminate\Support\HtmlString("<span style='color: red; font-weight: bold;'>{$text} (Lebih Rp " . number_format(abs($sisa), 0, ',', '.') . ")</span>");
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
                    ->copyMessageDuration(1500)
                    ->toggleable(),
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
                    ->sortable()
                    ->toggleable(),
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
                        'gray' => 'draft',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('jenis_spm')
                    ->label('Jenis SPM')
                    ->options(function () {
                        return Sp2dRekap::query()
                            ->select('jenis_spm')
                            ->distinct()
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
                        'draft' => 'Draft',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
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
