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
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
                Section::make('Data Pegawai')
                    ->description('Daftarkan pegawai untuk pengelolaan aset BMN')
                    ->icon('heroicon-o-user-circle')
                    ->schema([

                        // ── Fieldset 1: Sinkronisasi dari User ──────────────────
                        Fieldset::make('Sinkronisasi Akun')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Pilih dari Manajemen User')
                                    ->prefixIcon('heroicon-m-user')
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: function ($query) {
                                            $user = auth()->user();
                                            $query->role('pegawai')->active();

                                            // Jika bukan super_admin, operator, atau ketua_tim
                                            // maka hanya tampilkan diri sendiri
                                            if (
                                                ! $user->hasAnyRole(['super_admin', 'operator', 'ketua_tim'])
                                            ) {
                                                $query->where('id', $user->id);
                                            }

                                            return $query;
                                        }
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
                                                $set('aktif', $user->is_active ? '1' : '0');
                                            }
                                        }
                                    })
                                    ->helperText('Pilih user dengan role pegawai. Data identitas di bawah akan terisi otomatis.')
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),

                        // ── Fieldset 2: Identitas Pegawai ───────────────────────
                        Fieldset::make('Identitas Pegawai')
                            ->schema([
                                Group::make([
                                    TextInput::make('nama')
                                        ->label('Nama Lengkap')
                                        ->prefixIcon('heroicon-m-identification')
                                        ->required()
                                        ->placeholder('Nama lengkap sesuai data'),

                                    TextInput::make('nip')
                                        ->label('NIP')
                                        ->prefixIcon('heroicon-m-hashtag')
                                        ->placeholder('–'),
                                ])->columns(2)->columnSpanFull(),

                                Group::make([
                                    TextInput::make('jabatan')
                                        ->label('Jabatan')
                                        ->prefixIcon('heroicon-m-briefcase')
                                        ->placeholder('Jabatan / posisi pegawai'),

                                    TextInput::make('no_hp')
                                        ->label('No. HP / WA')
                                        ->prefixIcon('heroicon-m-phone')
                                        ->tel()
                                        ->placeholder('08xxxxxxxxxx'),
                                ])->columns(2)->columnSpanFull(),
                            ])->columnSpanFull(),

                        // ── Fieldset 3: Status ───────────────────────────────────
                        Fieldset::make('Status Kepegawaian')
                            ->schema([
                                ToggleButtons::make('aktif')
                                    ->label('Status Aktif')
                                    ->boolean()
                                    ->options([
                                        true  => 'Aktif',
                                        false => 'Tidak Aktif',
                                    ])
                                    ->colors([
                                        true  => 'success',
                                        false => 'danger',
                                    ])
                                    ->icons([
                                        true  => 'heroicon-m-check-circle',
                                        false => 'heroicon-m-x-circle',
                                    ])
                                    ->default(true)
                                    ->inline()
                                    ->grouped()
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),

                    ])->columnSpanFull(),
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
