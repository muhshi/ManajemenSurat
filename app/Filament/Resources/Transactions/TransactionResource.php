<?php

namespace App\Filament\Resources\Transactions;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Data Transaksi (SEP-BP)';
    protected static string | \UnitEnum | null $navigationGroup = 'Inventaris';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only info if users click View
                DatePicker::make('tanggal'),
                TextInput::make('keterangan'),
                TextInput::make('no_dok'),
                // Masuk
                TextInput::make('masuk_unit'),
                TextInput::make('masuk_harga'),
                TextInput::make('masuk_jumlah'),
                // Keluar
                TextInput::make('keluar_unit'),
                TextInput::make('keluar_harga'),
                TextInput::make('keluar_jumlah'),
                // Saldo
                TextInput::make('saldo_unit'),
                TextInput::make('saldo_harga'),
                TextInput::make('saldo_jumlah'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('item.item_code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.item_name')
                    ->label('Nama Barang')
                    ->searchable(),
                TextColumn::make('item.satuan')
                    ->label('Satuan'),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('no_dok')
                    ->label('No. Dokumen')
                    ->searchable(),
                TextColumn::make('masuk_unit')
                    ->label('Masuk (Unit)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('masuk_jumlah')
                    ->label('Masuk (Rp)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('keluar_unit')
                    ->label('Keluar (Unit)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('keluar_jumlah')
                    ->label('Keluar (Rp)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('saldo_unit')
                    ->label('Saldo (Unit)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('saldo_jumlah')
                    ->label('Saldo (Rp)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('inventoryUpload.period_start')
                    ->label('Dari Upload Periode')
                    ->date('m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('item_id')
                    ->label('Barang')
                    ->relationship('item', 'item_code')
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->item_code} - {$record->item_name}"),
                Filter::make('tanggal')
                    ->schema([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListTransactions::route('/'),
        ];
    }
}
