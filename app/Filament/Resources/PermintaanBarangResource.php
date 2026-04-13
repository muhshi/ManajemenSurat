<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\SignaturePad;
use App\Filament\Resources\PermintaanBarangResource\Pages;
use App\Models\PermintaanBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermintaanBarangResource extends Resource
{
    protected static ?string $model = PermintaanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Permintaan Barang';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?string $modelLabel = 'Permintaan Barang';
    protected static ?string $pluralModelLabel = 'Permintaan Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Peminta')
                    ->schema([
                        Forms\Components\TextInput::make('nama_peminta')
                            ->label('Nama Peminta')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Daftar Barang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Item Barang')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('nama_item')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                Forms\Components\TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->placeholder('buah, rim, pak, dll.'),
                                Forms\Components\TextInput::make('keterangan')
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

                Forms\Components\Section::make('Tanda Tangan')
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
                Tables\Columns\TextColumn::make('nama_peminta')
                    ->label('Nama Peminta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('signature')
                    ->label('TTD')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => !empty($record->signature)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $d) => $q->whereDate('tanggal', '>=', $d))
                            ->when($data['sampai'], fn ($q, $d) => $q->whereDate('tanggal', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPermintaanBarangs::route('/'),
            'create' => Pages\CreatePermintaanBarang::route('/create'),
            'edit' => Pages\EditPermintaanBarang::route('/{record}/edit'),
            'view' => Pages\ViewPermintaanBarang::route('/{record}'),
        ];
    }
}
