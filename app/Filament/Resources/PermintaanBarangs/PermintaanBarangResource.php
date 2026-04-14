<?php

namespace App\Filament\Resources\PermintaanBarangs;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PermintaanBarangs\Pages\ListPermintaanBarangs;
use App\Filament\Resources\PermintaanBarangs\Pages\CreatePermintaanBarang;
use App\Filament\Resources\PermintaanBarangs\Pages\EditPermintaanBarang;
use App\Filament\Resources\PermintaanBarangs\Pages\ViewPermintaanBarang;
use App\Filament\Forms\Components\SignaturePad;
use App\Filament\Resources\PermintaanBarangResource\Pages;
use App\Models\PermintaanBarang;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermintaanBarangResource extends Resource
{
    protected static ?string $model = PermintaanBarang::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Permintaan Barang';
    protected static string | \UnitEnum | null $navigationGroup = 'Inventaris';
    protected static ?string $modelLabel = 'Permintaan Barang';
    protected static ?string $pluralModelLabel = 'Permintaan Barang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peminta')
                    ->schema([
                        TextInput::make('nama_peminta')
                            ->label('Nama Peminta')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Section::make('Daftar Barang')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Barang')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('nama_item')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->placeholder('buah, rim, pak, dll.'),
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->columnSpan(2),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Barang')
                            ->reorderable()
                            ->cloneable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Tanda Tangan')
                    ->schema([
                        SignaturePad::make('signature')
                            ->label('Tanda Tangan Peminta')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_peminta')
                    ->label('Nama Peminta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                IconColumn::make('signature')
                    ->label('TTD')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => !empty($record->signature)),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Filter::make('tanggal')
                    ->schema([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $d) => $q->whereDate('tanggal', '>=', $d))
                            ->when($data['sampai'], fn ($q, $d) => $q->whereDate('tanggal', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermintaanBarangs::route('/'),
            'create' => CreatePermintaanBarang::route('/create'),
            'edit' => EditPermintaanBarang::route('/{record}/edit'),
            'view' => ViewPermintaanBarang::route('/{record}'),
        ];
    }
}
