<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages\CreatePegawai;
use App\Filament\Resources\PegawaiResource\Pages\EditPegawai;
use App\Filament\Resources\PegawaiResource\Pages\ListPegawais;
use App\Filament\Resources\PegawaiResource\RelationManagers\BmnsRelationManager;
use App\Models\Pegawai;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen BMN';

    protected static ?string $navigationLabel = 'Pegawai';

    protected static ?string $modelLabel = 'Pegawai';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([
                    Select::make('user_id')
                        ->label('Pilih dari Manajemen User')
                        ->relationship(
                            name: 'user',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->role('pegawai')->active()
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $user = \App\Models\User::find($state);
                                if ($user) {
                                    $set('nama', $user->name);
                                    $set('nip', $user->nip ?? $user->nip_baru);
                                    $set('jabatan', $user->jabatan);
                                    $set('no_hp', $user->nomor_hp);
                                    $set('aktif', $user->is_active);
                                }
                            }
                        })
                        ->helperText('Pilih user dengan role pegawai. Data di bawah akan terisi otomatis.'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('nama')
                        ->label('Nama Lengkap')
                        ->required(),

                    TextInput::make('nip')
                        ->label('NIP'),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('jabatan')
                        ->label('Jabatan'),

                    TextInput::make('no_hp')
                        ->label('No. HP / WA')
                        ->tel(),
                ]),

                Toggle::make('aktif')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->placeholder('–')
                    ->searchable(),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->placeholder('–'),

                TextColumn::make('bmns_count')
                    ->label('Jumlah BMN Ditanggung')
                    ->counts('bmns')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('aktif')->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('nama');
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
            'index' => ListPegawais::route('/'),
            'create' => CreatePegawai::route('/create'),
            'edit' => EditPegawai::route('/{record}/edit'),
        ];
    }
}
