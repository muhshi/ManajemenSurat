<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratMasukResource\Pages;
use App\Filament\Resources\SuratMasukResource\RelationManagers;
use App\Models\SuratMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class SuratMasukResource extends Resource
{
    protected static ?string $model = SuratMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Arsip Digital')
                    ->description('Upload file surat untuk ekstraksi data otomatis')
                    ->schema([
                        Forms\Components\FileUpload::make('file_surat')
                            ->label('File Surat (PDF/Gambar)')
                            ->directory('surat-masuk')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->live()
                            ->columnSpanFull()
                            ->hintAction(
                                Forms\Components\Actions\Action::make('extractAi')
                                    ->label('Ekstrak dengan Gemini AI')
                                    ->icon('heroicon-m-sparkles')
                                    ->color('success')
                                    ->action(function (Forms\Get $get, Forms\Set $set, \App\Services\GeminiService $gemini) {

                                        $state = $get('file_surat');

                                        \Log::info('Upload state', [
                                            'state' => $state,
                                            'type' => gettype($state),
                                        ]);

                                        if (!is_array($state) || empty($state)) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('File belum siap')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
                                        $file = collect($state)->first();

                                        if (!$file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('File upload tidak valid')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        $path = $file->getRealPath(); // ✅ VALID UNTUK TEMP FILE
                            
                                        $data = $gemini->extractMetadata($path);

                                        if (!$data) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Ekstraksi gagal')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        foreach ($data as $key => $value) {
                                            $set($key, $value);
                                        }

                                        \Filament\Notifications\Notification::make()
                                            ->title('Ekstraksi berhasil')
                                            ->success()
                                            ->send();
                                    })

                            ),
                    ]),

                Forms\Components\Section::make('Identitas Pengirim')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pengirim')
                            ->label('Nama Pengirim'),
                        Forms\Components\TextInput::make('jabatan_pengirim')
                            ->label('Jabatan Pengirim'),
                        Forms\Components\TextInput::make('instansi_pengirim')
                            ->label('Instansi Pengirim')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Surat')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_surat')
                            ->label('Nomor Surat'),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->label('Tanggal Surat'),
                        Forms\Components\DatePicker::make('tanggal_diterima')
                            ->label('Tanggal Diterima')
                            ->default(now()),
                        Forms\Components\TextInput::make('perihal')
                            ->label('Perihal')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('isi_ringkas')
                            ->label('Isi Ringkas')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pengirim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan_pengirim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('instansi_pengirim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSuratMasuks::route('/'),
            'create' => Pages\CreateSuratMasuk::route('/create'),
            'edit' => Pages\EditSuratMasuk::route('/{record}/edit'),
        ];
    }
}
