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
                \Filament\Schemas\Components\Section::make('Rincian SP2D')
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
                        Forms\Components\TextInput::make('jumlah_potongan')
                            ->label('Target Potongan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('atas_nama_default')
                            ->label('Atas Nama Default'),
                        Forms\Components\Select::make('status_verifikasi')
                            ->label('Status Verifikasi')
                            ->options([
                                'draft' => 'Draft',
                                'perlu_rincian' => 'Perlu Rincian',
                                'valid' => 'Valid',
                            ])
                            ->required(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Rincian Pajak')
                    ->description('Untuk SP2D Jalur Banyak Pihak, pastikan Total Pajak sama dengan Target Potongan.')
                    ->schema([
                        Forms\Components\Repeater::make('pajaks')
                            ->relationship()
                            ->label('Daftar Pajak Pihak/Penerima')
                            ->schema([
                                Forms\Components\TextInput::make('npwp_nik')
                                    ->label('NPWP / NIK'),
                                Forms\Components\TextInput::make('nama_pihak')
                                    ->label('Nama Pihak')
                                    ->required(),
                                Forms\Components\Select::make('kode_akun_pajak')
                                    ->label('Jenis/Akun Pajak')
                                    ->options([
                                        '411121' => '411121 - PPh 21',
                                        '411122' => '411122 - PPh 22',
                                        '411124' => '411124 - PPh 23',
                                        '411211' => '411211 - PPN',
                                        '411128' => '411128 - PPh Final',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('dpp')
                                    ->label('DPP')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('nominal_pajak')
                                    ->label('Nominal Pajak (Rp)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Rincian Pajak')
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
                Tables\Columns\TextColumn::make('jumlah_pengeluaran')
                    ->label('Bruto')
                    ->money('IDR', locale: 'id')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
                \Filament\Actions\EditAction::make(),
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
            'edit' => Pages\EditSp2dRekap::route('/{record}/edit'),
        ];
    }
}
