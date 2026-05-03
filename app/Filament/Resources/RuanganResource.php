<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RuanganResource\Pages\CreateRuangan;
use App\Filament\Resources\RuanganResource\Pages\EditRuangan;
use App\Filament\Resources\RuanganResource\Pages\ListRuangans;
use App\Filament\Resources\RuanganResource\RelationManagers\BmnsRelationManager;
use App\Models\Ruangan;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RuanganResource extends Resource
{
    protected static ?string $model = Ruangan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen BMN';

    protected static ?string $navigationLabel = 'Ruangan';

    protected static ?string $modelLabel = 'Ruangan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Ruangan')
                    ->description('Masukkan data ruangan gedung')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Fieldset::make('Informasi Utama')
                            ->schema([
                                Group::make([
                                    TextInput::make('kode_ruang')
                                        ->label('Kode Ruang')
                                        ->prefixIcon('heroicon-m-hashtag')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->placeholder('1001'),

                                    Select::make('lantai')
                                        ->label('Lantai')
                                        ->prefixIcon('heroicon-m-building-office-2')
                                        ->options([1 => 'Lantai 1', 2 => 'Lantai 2', 3 => 'Lantai 3'])
                                        ->default(1)
                                        ->required(),
                                ])->columns(2)->columnSpanFull(),

                                Select::make('nama_tipe_ruang')
                                    ->label('Tipe Ruang')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->options([
                                        'Ruang Kerja' => 'Ruang Kerja',
                                        'Ruang Pelayanan' => 'Ruang Pelayanan',
                                        'Ruang Istirahat' => 'Ruang Istirahat',
                                        'Ruang Toilet/WC' => 'Ruang Toilet/WC',
                                        'Ruang Gudang' => 'Ruang Gudang',
                                        'Ruang Rapat Besar' => 'Ruang Rapat Besar',
                                        'Ruang Ibadah' => 'Ruang Ibadah',
                                    ])
                                    ->searchable()
                                    ->columnSpanFull(),

                                TextInput::make('nama_ruang')
                                    ->label('Nama Ruang')
                                    ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                                    ->required()
                                    ->placeholder('RUANG PELAYANAN PUBLIK')
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),

                        Fieldset::make('Detail Tambahan')
                            ->schema([
                                Group::make([
                                    TextInput::make('luas_ruang')
                                        ->label('Luas Ruang (m²)')
                                        ->prefixIcon('heroicon-m-arrows-pointing-out')
                                        ->numeric()
                                        ->suffix('m²'),

                                    TextInput::make('gedung')
                                        ->label('Nama Gedung')
                                        ->prefixIcon('heroicon-m-building-office-2')
                                        ->placeholder('Kosongkan jika satu gedung'),
                                ])->columns(2)->columnSpanFull(),
                            ])->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_ruang')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_tipe_ruang')
                    ->label('Tipe')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('nama_ruang')
                    ->label('Nama Ruang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lantai')
                    ->label('Lantai')
                    ->formatStateUsing(fn($state) => 'Lantai ' . $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('luas_ruang')
                    ->label('Luas (m²)')
                    ->suffix(' m²')
                    ->sortable(),

                TextColumn::make('bmns_count')
                    ->label('Jumlah BMN')
                    ->counts('bmns')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('lantai')
                    ->options([1 => 'Lantai 1', 2 => 'Lantai 2', 3 => 'Lantai 3']),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('kode_ruang');
    }

    public static function getRelations(): array
    {
        return [
            BmnsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRuangans::route('/'),
            'create' => CreateRuangan::route('/create'),
            'edit' => EditRuangan::route('/{record}/edit'),
        ];
    }
}
