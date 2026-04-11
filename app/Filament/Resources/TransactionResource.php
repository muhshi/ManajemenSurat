<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Data Transaksi (SEP-BP)';
    protected static ?string $navigationGroup = 'Inventaris';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only info if users click View
                Forms\Components\DatePicker::make('tanggal'),
                Forms\Components\TextInput::make('keterangan'),
                Forms\Components\TextInput::make('no_dok'),
                // Masuk
                Forms\Components\TextInput::make('masuk_unit'),
                Forms\Components\TextInput::make('masuk_harga'),
                Forms\Components\TextInput::make('masuk_jumlah'),
                // Keluar
                Forms\Components\TextInput::make('keluar_unit'),
                Forms\Components\TextInput::make('keluar_harga'),
                Forms\Components\TextInput::make('keluar_jumlah'),
                // Saldo
                Forms\Components\TextInput::make('saldo_unit'),
                Forms\Components\TextInput::make('saldo_harga'),
                Forms\Components\TextInput::make('saldo_jumlah'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_name')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item.satuan')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_dok')
                    ->label('No. Dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('masuk_unit')
                    ->label('Masuk (Unit)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('masuk_jumlah')
                    ->label('Masuk (Rp)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keluar_unit')
                    ->label('Keluar (Unit)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keluar_jumlah')
                    ->label('Keluar (Rp)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_unit')
                    ->label('Saldo (Unit)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_jumlah')
                    ->label('Saldo (Rp)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('inventoryUpload.period_start')
                    ->label('Dari Upload Periode')
                    ->date('m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('item_id')
                    ->label('Barang')
                    ->relationship('item', 'item_code')
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->item_code} - {$record->item_name}"),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
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
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
