<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunPajakResource\Pages;
use App\Models\AkunPajak;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class AkunPajakResource extends Resource
{
    protected static ?string $model = AkunPajak::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Master Akun Pajak';
    protected static ?string $pluralModelLabel = 'Master Akun Pajak';
    protected static string|\UnitEnum|null $navigationGroup = 'Rekap SP2D';
    
    // Add sorting
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode Akun Pajak')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('nama_pendek')
                    ->label('Nama Pendek (Singkatan)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap (Uraian)')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_pendek')
                    ->label('Singkatan')
                    ->searchable(),
                TextColumn::make('nama_lengkap')
                    ->label('Uraian Lengkap')
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kode', 'asc');
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
            'index' => Pages\ListAkunPajaks::route('/'),
        ];
    }
}
