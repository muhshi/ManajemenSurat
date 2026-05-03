<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

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
                Section::make('Form Kelola Pengguna')
                    ->description('Manajemen profil dan akses pengguna')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Tabs::make('Tabs')
                            ->tabs([
                                Tab::make('Akun & Dasar')
                                    ->icon('heroicon-o-user-circle')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('name')
                                                ->label('Nama Lengkap')
                                                ->prefixIcon('heroicon-m-user')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->label('Alamat Email')
                                                ->prefixIcon('heroicon-m-envelope')
                                                ->email()
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255),
                                        ])->columns(2)->columnSpanFull(),
                                        Group::make([
                                            TextInput::make('password')
                                                ->label('Password')
                                                ->prefixIcon('heroicon-m-lock-closed')
                                                ->password()
                                                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                                ->dehydrated(fn($state) => filled($state))
                                                ->required(fn(string $context): bool => $context === 'create')
                                                ->placeholder('Kosongkan jika tidak ingin mengubah password'),
                                            Select::make('roles')
                                                ->label('Role / Peran')
                                                ->prefixIcon('heroicon-m-shield-check')
                                                ->relationship('roles', 'name')
                                                ->multiple()
                                                ->preload()
                                                ->searchable(),
                                        ])->columns(2)->columnSpanFull(),
                                        Group::make([
                                            Placeholder::make('current_avatar')
                                                ->label('Foto Saat Ini')
                                                ->content(function ($record) {
                                                    if (!$record?->avatar_url) return 'Belum ada foto';
                                                    $url = filter_var($record->avatar_url, FILTER_VALIDATE_URL)
                                                        ? $record->avatar_url
                                                        : asset('storage/' . $record->avatar_url);
                                                    return new HtmlString("<img src='$url' class='w-20 h-20 rounded-full object-cover shadow border'>");
                                                }),
                                            FileUpload::make('avatar_url')
                                                ->label('Ganti Foto Profil')
                                                ->image()
                                                ->avatar()
                                                ->directory('avatars')
                                                ->helperText('Unggah untuk mengganti atau membiarkannya tetap jika menggunakan foto SSO.'),
                                        ])->columns(2)->columnSpanFull(),
                                    ]),

                                Tab::make('Identitas Pegawai')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('nip')
                                                ->label('NIP (Lama)')
                                                ->prefixIcon('heroicon-m-identification')
                                                ->maxLength(20),
                                            TextInput::make('nip_baru')
                                                ->label('NIP Baru')
                                                ->prefixIcon('heroicon-m-identification')
                                                ->maxLength(20),
                                        ])->columns(2)->columnSpanFull(),
                                        Group::make([
                                            TextInput::make('sobat_id')
                                                ->label('Sobat ID')
                                                ->prefixIcon('heroicon-m-hashtag')
                                                ->maxLength(50),
                                            TextInput::make('nomor_hp')
                                                ->label('Nomor HP / WhatsApp')
                                                ->prefixIcon('heroicon-m-device-phone-mobile')
                                                ->tel()
                                                ->maxLength(20),
                                        ])->columns(2)->columnSpanFull(),
                                        Group::make([
                                            Select::make('gender')
                                                ->label('Jenis Kelamin')
                                                ->prefixIcon('heroicon-m-users')
                                                ->options([
                                                    'L' => 'Laki-laki',
                                                    'P' => 'Perempuan',
                                                ]),
                                            TextInput::make('identity_type')
                                                ->label('Tipe Identitas')
                                                ->prefixIcon('heroicon-m-tag')
                                                ->placeholder('Pegawai / Mitra'),
                                        ])->columns(2)->columnSpanFull(),
                                    ]),

                                Tab::make('Organisasi')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('jabatan')
                                                ->label('Jabatan')
                                                ->prefixIcon('heroicon-m-briefcase')
                                                ->maxLength(255),
                                            TextInput::make('golongan')
                                                ->label('Golongan')
                                                ->prefixIcon('heroicon-m-academic-cap')
                                                ->maxLength(50),
                                        ])->columns(2)->columnSpanFull(),
                                        Group::make([
                                            TextInput::make('kd_satker')
                                                ->label('Kode Satker')
                                                ->prefixIcon('heroicon-m-building-office')
                                                ->maxLength(10),
                                            TextInput::make('unit_kerja')
                                                ->label('Unit Kerja')
                                                ->prefixIcon('heroicon-m-building-office-2')
                                                ->maxLength(255),
                                        ])->columns(2)->columnSpanFull(),
                                    ]),

                                Tab::make('Data Tambahan')
                                    ->icon('heroicon-o-document-plus')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('tempat_lahir')
                                                ->label('Tempat Lahir')
                                                ->prefixIcon('heroicon-m-map-pin'),
                                            TextInput::make('tanggal_lahir')
                                                ->label('Tanggal Lahir')
                                                ->prefixIcon('heroicon-m-calendar')
                                                ->placeholder('YYYY-MM-DD'),
                                        ])->columns(2)->columnSpanFull(),
                                        TextInput::make('pendidikan')
                                            ->label('Pendidikan Terakhir')
                                            ->prefixIcon('heroicon-m-academic-cap')
                                            ->columnSpanFull(),
                                    ]),

                                Tab::make('SSO Sipetra')
                                    ->icon('heroicon-o-key')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('sipetra_id')
                                                ->label('Sipetra ID')
                                                ->prefixIcon('heroicon-m-key')
                                                ->readOnly(),
                                            TextInput::make('sipetra_token')
                                                ->label('Access Token')
                                                ->prefixIcon('heroicon-m-lock-closed')
                                                ->readOnly()
                                                ->password(),
                                        ])->columns(2)->columnSpanFull(),
                                    ]),
                            ])->columnSpanFull(),
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
            ->recordActions([
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
