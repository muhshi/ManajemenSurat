<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratMasukResource\Pages;
use App\Filament\Resources\SuratMasukResource\RelationManagers;
use App\Models\SuratMasuk;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
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

    protected static ?string $navigationIcon = 'heroicon-s-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Surat Masuk';

    protected static ?string $navigationLabel = 'Surat Masuk';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Arsip Digital')
                    ->description('Upload file surat untuk ekstraksi data otomatis')
                    ->schema([
                        AdvancedFileUpload::make('file_surat')
                            ->label('File Surat (PDF/Gambar)')
                            ->directory('surat-masuk')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->live()
                            ->pdfPreviewHeight(400)
                            ->pdfToolbar(true)
                            ->pdfNavPanes(true)
                            ->pdfZoomLevel(100)
                            ->pdfFitType(PdfViewFit::FIT)
                            ->pdfDisplayPage(1)
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'Kepala', 'Kasubag'])) {
            return $query;
        }

        return $query->whereHas('disposisis', function ($q) use ($user) {
            $q->where('penerima_id', $user->id);
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pengirim')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn(Tables\Columns\TextColumn $column): ?string => $column->getState()),
                Tables\Columns\TextColumn::make('instansi_pengirim')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn(Tables\Columns\TextColumn $column): ?string => $column->getState()),
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('perihal')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn(Tables\Columns\TextColumn $column): ?string => $column->getState()),
                Tables\Columns\TextColumn::make('disposisi_status')
                    ->label('Status Disposisi')
                    ->state(function (SuratMasuk $record): string {
                        $stats = $record->disposisis()
                            ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                            ->groupBy('status')
                            ->get()
                            ->map(fn($item) => "{$item->status}: {$item->total}")
                            ->implode(', ');

                        return $stats ?: 'Belum Ada';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 'Belum Ada' => 'gray',
                        str_contains($state, 'Belum Dibaca') => 'danger',
                        str_contains($state, 'Dilihat') && !str_contains($state, 'Belum Dibaca') => 'warning',
                        str_contains($state, 'Selesai') && !str_contains($state, 'Dilihat') && !str_contains($state, 'Belum Dibaca') => 'success',
                        default => 'info',
                    })
                    ->action(
                        Tables\Actions\Action::make('viewDisposisiStatus')
                            ->modalHeading('Rincian Status Disposisi')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->form([
                                Forms\Components\Placeholder::make('status_per_user')
                                    ->label('Daftar Penerima & Status')
                                    ->content(fn(SuratMasuk $record) => new \Illuminate\Support\HtmlString(
                                        '<div class="divide-y divide-gray-200 border rounded-lg overflow-hidden">' .
                                        $record->disposisis->map(fn($d) => "
                                            <div class='flex justify-between items-center p-3 bg-white'>
                                                <div class='flex flex-col'>
                                                    <span class='font-semibold text-gray-900'>{$d->penerima->name}</span>
                                                    <span class='text-xs text-gray-500'>Dikirim: {$d->created_at->format('d M Y H:i')}</span>
                                                </div>
                                                <div class='flex flex-col items-end gap-1'>
                                                    <span class='px-2 py-1 text-xs font-medium rounded-full " . match ($d->status) {
                                            'Belum Dibaca' => 'bg-red-100 text-red-700',
                                            'Dilihat' => 'bg-yellow-100 text-yellow-700',
                                            'Selesai' => 'bg-green-100 text-green-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        } . "'>{$d->status}</span>
                                                    " . ($d->catatan ? "<span class='text-[10px] text-gray-400 italic max-w-[150px] truncate' title='{$d->catatan}'>\"{$d->catatan}\"</span>" : "") . "
                                                </div>
                                            </div>
                                        ")->implode('') .
                                        '</div>'
                                    ))
                            ])
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('disposisi')
                    ->label(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'Selesai Disposisi' : 'Disposisi')
                    ->icon(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'heroicon-s-check-circle' : 'heroicon-o-paper-airplane')
                    ->color(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'success' : 'info')
                    ->modalHeading(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'Riwayat & Tambah Disposisi' : 'Input Disposisi')
                    ->form([
                        Forms\Components\Placeholder::make('existing_disposisis')
                            ->label('Riwayat Disposisi')
                            ->visible(fn(SuratMasuk $record) => $record->disposisis()->exists())
                            ->content(fn(SuratMasuk $record) => new \Illuminate\Support\HtmlString(
                                '<div class="space-y-2">' .
                                $record->disposisis->map(
                                    fn($d) =>
                                    "<div class='p-2 bg-gray-50 rounded border border-gray-200'>
                                        <strong>Ke:</strong> {$d->penerima->name} <br/>
                                        <strong>Sifat:</strong> {$d->sifat} <br/>
                                        <strong>Catatan:</strong> " . ($d->catatan ?: '-') . "
                                    </div>"
                                )->implode('') .
                                '</div>'
                            )),
                        Forms\Components\Section::make('Tambah Disposisi Baru')
                            ->schema([
                                Forms\Components\Select::make('penerima_ids')
                                    ->label('Pilih Penerima')
                                    ->multiple()
                                    ->options(\App\Models\User::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('sifat')
                                    ->options([
                                        'Biasa' => 'Biasa',
                                        'Segera' => 'Segera',
                                        'Penting' => 'Penting',
                                        'Rahasia' => 'Rahasia',
                                    ])
                                    ->default('Biasa')
                                    ->required(),
                                Forms\Components\Textarea::make('catatan')
                                    ->label('Instruksi / Catatan')
                                    ->rows(3),
                            ])
                    ])
                    ->action(function (SuratMasuk $record, array $data) {
                        foreach ($data['penerima_ids'] as $penerimaId) {
                            $record->disposisis()->create([
                                'penerima_id' => $penerimaId,
                                'pengirim_id' => auth()->id(),
                                'sifat' => $data['sifat'],
                                'catatan' => $data['catatan'],
                                'status' => 'Belum Dibaca',
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Disposisi berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
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
