<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Manajemen User';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Manajemen User';


    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Akun & Dasar')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->label('Alamat Email')
                                        ->email()
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                        ->dehydrated(fn($state) => filled($state))
                                        ->required(fn(string $context): bool => $context === 'create')
                                        ->placeholder('Kosongkan jika tidak ingin mengubah password'),
                                    Select::make('roles')
                                        ->label('Role / Peran')
                                        ->relationship('roles', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->searchable(),
                                ]),
                                FileUpload::make('avatar_url')
                                    ->label('Foto Profil')
                                    ->image()
                                    ->avatar()
                                    ->circleDimensions()
                                    ->directory('avatars'),
                            ]),

                        Tab::make('Identitas Pegawai')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('nip')
                                        ->label('NIP (Lama)')
                                        ->maxLength(20),
                                    TextInput::make('nip_baru')
                                        ->label('NIP Baru')
                                        ->maxLength(20),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('sobat_id')
                                        ->label('Sobat ID')
                                        ->maxLength(50),
                                    TextInput::make('nomor_hp')
                                        ->label('Nomor HP / WhatsApp')
                                        ->tel()
                                        ->maxLength(20),
                                ]),
                                Grid::make(2)->schema([
                                    Select::make('gender')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'L' => 'Laki-laki',
                                            'P' => 'Perempuan',
                                        ]),
                                    TextInput::make('identity_type')
                                        ->label('Tipe Identitas')
                                        ->placeholder('Pegawai / Mitra'),
                                ]),
                            ]),

                        Tab::make('Organisasi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('jabatan')
                                        ->label('Jabatan')
                                        ->maxLength(255),
                                    TextInput::make('golongan')
                                        ->label('Golongan')
                                        ->maxLength(50),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('kd_satker')
                                        ->label('Kode Satker')
                                        ->maxLength(10),
                                    TextInput::make('unit_kerja')
                                        ->label('Unit Kerja')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Tab::make('Data Tambahan')
                            ->icon('heroicon-o-document-plus')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('tempat_lahir')
                                        ->label('Tempat Lahir'),
                                    TextInput::make('tanggal_lahir')
                                        ->label('Tanggal Lahir')
                                        ->placeholder('YYYY-MM-DD'),
                                ]),
                                TextInput::make('pendidikan')
                                    ->label('Pendidikan Terakhir'),
                            ]),

                        Tab::make('SSO Sipetra')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('sipetra_id')
                                        ->label('Sipetra ID')
                                        ->readOnly(),
                                    TextInput::make('sipetra_token')
                                        ->label('Access Token')
                                        ->readOnly()
                                        ->password(),
                                ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
