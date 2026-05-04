<?php

namespace App\Filament\Resources\Agendas\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PesertaRelationManager extends RelationManager
{
    protected static string $relationship = 'peserta';
    protected static ?string $title = 'Daftar Peserta';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_search')
                    ->label('Cari & Pilih Pegawai/Mitra (Otomatis isi form)')
                    ->options(\App\Models\User::active()->pluck('name', 'id'))
                    ->searchable()
                    ->dehydrated(false) // Tidak disimpan ke database
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $user = \App\Models\User::find($state);
                            if ($user) {
                                $set('nama', $user->name);
                                $set('jabatan', $user->jabatan ?? '-');
                                $set('no_hp', $user->nomor_hp);
                            }
                        }
                    }),
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('jabatan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('no_hp')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20),
                Toggle::make('hadir')
                    ->label('Hadir')
                    ->default(true),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan'),
                Tables\Columns\IconColumn::make('hadir')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('massAdd')
                    ->label('Tambah Peserta (Pilih Pegawai/Mitra)')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Section::make('Pilih Peserta')->schema([
                            Select::make('pegawai_users')
                                ->label('Pegawai')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    return \App\Models\User::pegawai()
                                        ->get()
                                        ->mapWithKeys(function ($user) {
                                            $jabatan = $user->jabatan ?? '-';
                                            return [$user->id => "{$user->name} ({$jabatan})"];
                                        });
                                })
                                ->helperText('Cari dan pilih pegawai (bisa lebih dari satu)'),

                            Select::make('mitra_users')
                                ->label('Mitra')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    return \App\Models\User::mitra()
                                        ->get()
                                        ->mapWithKeys(function ($user) {
                                            $jabatan = $user->jabatan ?? '-';
                                            return [$user->id => "{$user->name} ({$jabatan})"];
                                        });
                                })
                                ->helperText('Cari dan pilih mitra (bisa lebih dari satu)'),
                        ])->columns(2),
                    ])
                    ->action(function (array $data, $livewire) {
                        $agenda = $livewire->getOwnerRecord();
                        $pegawaiUsers = $data['pegawai_users'] ?? [];
                        $mitraUsers = $data['mitra_users'] ?? [];
                        $allUserIds = array_merge($pegawaiUsers, $mitraUsers);

                        if (empty($allUserIds)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Pilih minimal 1 peserta')
                                ->danger()
                                ->send();
                            return;
                        }

                        $currentMaxUrutan = $agenda->peserta()->max('urutan') ?? 0;
                        $users = \App\Models\User::whereIn('id', $allUserIds)->get();

                        foreach ($users as $user) {
                            // Cek apakah sudah ada peserta dengan nama ini
                            $exists = $agenda->peserta()->where('nama', $user->name)->exists();

                            if (!$exists) {
                                $currentMaxUrutan++;
                                $agenda->peserta()->create([
                                    'nama' => $user->name,
                                    'jabatan' => $user->jabatan ?? '-',
                                    'no_hp' => $user->nomor_hp,
                                    'hadir' => true,
                                    'urutan' => $currentMaxUrutan,
                                ]);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Peserta berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan');
    }
}
