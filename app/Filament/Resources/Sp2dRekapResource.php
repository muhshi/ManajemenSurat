<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp2dRekapResource\Pages;
use App\Models\Sp2dRekap;
use App\Models\Sp2dPajak;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_spp')
                    ->label('No SPP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uraian_spp')
                    ->label('Uraian SPP')
                    ->limit(30)
                    ->tooltip(fn(Model $record): string => $record->uraian_spp)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_spp')
                    ->label('Tanggal SPP')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_pengeluaran')
                    ->label('Pengeluaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_potongan')
                    ->label('Potongan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_pembayaran')
                    ->label('Pembayaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pajak_summary')
                    ->label('Daftar Pajak')
                    ->getStateUsing(function (Sp2dRekap $record) {
                        if ($record->pajaks->isEmpty()) {
                            return ['+ Tambah Pajak'];
                        }
                        return $record->pajaks->map(function ($pajak) {
                            return $pajak->jenis_pajak . ' (Rp ' . number_format($pajak->jumlah_pajak, 0, ',', '.') . ')';
                        })->toArray();
                    })
                    ->badge()
                    ->color(fn(string $state) => $state === '+ Tambah Pajak' ? 'success' : 'info')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->listWithLineBreaks()
                    ->action(
                        Action::make('kelola_pajak')
                            ->modalHeading('Kelola Pajak')
                            ->form([
                                Forms\Components\Repeater::make('pajaks')
                                    ->label('Daftar Pajak')
                                    ->schema([
                                        Forms\Components\Select::make('jenis_pajak')
                                            ->label('Jenis Pajak')
                                            ->options(function (Get $get, ?string $state) {
                                                $allOptions = [
                                                    'PPN' => 'PPN',
                                                    'PPH21' => 'PPh 21',
                                                    'PPH22' => 'PPh 22',
                                                    'PPH23' => 'PPh 23',
                                                    'PPH_FINAL' => 'PPh Final',
                                                ];

                                                // Ambil list semua pajak yang sudah di-select di baris lain
                                                $selectedPajaks = collect($get('../../pajaks'))
                                                    ->pluck('jenis_pajak')
                                                    ->filter() // abaikan yang masih null
                                                    ->reject(fn($item) => $item === $state)
                                                    ->toArray();

                                                // Hapus pajak yang sudah kepilih dari $allOptions
                                                return collect($allOptions)
                                                    ->except($selectedPajaks)
                                                    ->toArray();
                                            })
                                            ->native(false)
                                            ->live()
                                            ->required(),
                                        Forms\Components\TextInput::make('jumlah_pajak')
                                            ->label('Jumlah Pajak (Rp)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Tambah Pajak Baru'),
                            ])
                            ->fillForm(fn(Sp2dRekap $record): array => [
                                'pajaks' => $record->pajaks->map(function ($pajak) {
                                    return [
                                        'jenis_pajak' => $pajak->jenis_pajak,
                                        'jumlah_pajak' => $pajak->jumlah_pajak,
                                    ];
                                })->toArray(),
                            ])
                            ->action(function (Sp2dRekap $record, array $data): void {
                                $record->pajaks()->delete();

                                foreach ($data['pajaks'] ?? [] as $pajakData) {
                                    if (!empty($pajakData['jenis_pajak']) && !empty($pajakData['jumlah_pajak'])) {
                                        $record->pajaks()->create([
                                            'jenis_pajak' => $pajakData['jenis_pajak'],
                                            'jumlah_pajak' => $pajakData['jumlah_pajak'],
                                        ]);
                                    }
                                }
                            })
                    ),
            ])
            ->filters([
                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(fn() => Sp2dRekap::distinct()->pluck('periode', 'periode')->toArray())
                    ->searchable(),
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
