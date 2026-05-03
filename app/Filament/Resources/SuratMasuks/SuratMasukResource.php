<?php

namespace App\Filament\Resources\SuratMasuks;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Services\GeminiService;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SuratMasuks\Pages\ListSuratMasuks;
use App\Filament\Resources\SuratMasuks\Pages\CreateSuratMasuk;
use App\Filament\Resources\SuratMasuks\Pages\EditSuratMasuk;
use App\Filament\Resources\SuratMasukResource\Pages;
use App\Filament\Resources\SuratMasukResource\RelationManagers;
use App\Models\SuratMasuk;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class SuratMasukResource extends Resource
{
    protected static ?string $model = SuratMasuk::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-inbox-arrow-down';
    protected static string | \UnitEnum | null $navigationGroup = 'Surat Masuk';

    protected static ?string $navigationLabel = 'Surat Masuk';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Surat Masuk')
                    ->description('Input data surat masuk dan upload arsip digital')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->schema([
                        Fieldset::make('Arsip Digital')
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
                                Action::make('extractAi')
                                    ->label('Ekstrak dengan Gemini AI')
                                    ->icon('heroicon-m-sparkles')
                                    ->color('success')
                                    ->action(function (Get $get, Set $set, GeminiService $gemini) {

                                        $state = $get('file_surat');

                                        if (empty($state)) {
                                            Notification::make()
                                                ->title('File belum siap')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        $path = null;
                                        $file = is_array($state) ? collect($state)->first() : $state;

                                        if ($file instanceof TemporaryUploadedFile) {
                                            $path = $file->getRealPath();
                                        } elseif (is_string($file)) {
                                            // Handle case where file is already saved in storage
                                            $path = \Illuminate\Support\Facades\Storage::disk('public')->path($file);
                                        }

                                        if (!$path || !file_exists($path)) {
                                            Notification::make()
                                                ->title('File fisik tidak ditemukan')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        try {
                                            $data = $gemini->extractMetadata($path);

                                            if (!$data) {
                                                Notification::make()
                                                    ->title('Ekstraksi gagal')
                                                    ->body('AI tidak mengembalikan data yang valid.')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            foreach ($data as $key => $value) {
                                                $set($key, $value);
                                            }

                                            Notification::make()
                                                ->title('Ekstraksi berhasil')
                                                ->success()
                                                ->send();

                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Ekstraksi Gagal')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->persistent()
                                                ->send();
                                            return;
                                        }
                                    })

                            ),
                            ])->columns(1)->columnSpanFull(),

                        Fieldset::make('Identitas Pengirim')
                            ->schema([
                                TextInput::make('nama_pengirim')
                                    ->label('Nama Pengirim')
                                    ->prefixIcon('heroicon-m-user'),
                                TextInput::make('jabatan_pengirim')
                                    ->label('Jabatan Pengirim')
                                    ->prefixIcon('heroicon-m-briefcase'),
                                TextInput::make('instansi_pengirim')
                                    ->label('Instansi Pengirim')
                                    ->prefixIcon('heroicon-m-building-office-2')
                                    ->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),

                        Fieldset::make('Detail Surat')
                            ->schema([
                                Group::make([
                                    TextInput::make('nomor_surat')
                                        ->label('Nomor Surat')
                                        ->prefixIcon('heroicon-m-hashtag'),
                                    DatePicker::make('tanggal_surat')
                                        ->label('Tanggal Surat')
                                        ->prefixIcon('heroicon-m-calendar'),
                                    DatePicker::make('tanggal_diterima')
                                        ->label('Tanggal Diterima')
                                        ->prefixIcon('heroicon-m-inbox-arrow-down')
                                        ->default(now()),
                                ])->columns(3)->columnSpanFull(),
                                TextInput::make('perihal')
                                    ->label('Perihal')
                                    ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                                    ->columnSpanFull(),
                                Textarea::make('isi_ringkas')
                                    ->label('Isi Ringkas')
                                    ->columnSpanFull(),
                            ])->columns(3)->columnSpanFull(),
                    ])->columnSpanFull(),
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
                TextColumn::make('nama_pengirim')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),
                TextColumn::make('instansi_pengirim')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),
                TextColumn::make('nomor_surat')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_surat')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('perihal')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),
                TextColumn::make('disposisi_status')
                    ->label('Status Disposisi')
                    ->state(function (SuratMasuk $record): string {
                        $stats = $record->disposisis()
                            ->select('status', DB::raw('count(*) as total'))
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
                        Action::make('viewDisposisiStatus')
                            ->modalHeading('Rincian Status Disposisi')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->schema([
                                Placeholder::make('status_per_user')
                                    ->label('Daftar Penerima & Status')
                                    ->content(fn(SuratMasuk $record) => new HtmlString(
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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('disposisi')
                    ->label(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'Selesai Disposisi' : 'Disposisi')
                    ->icon(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'heroicon-s-check-circle' : 'heroicon-o-paper-airplane')
                    ->color(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'success' : 'info')
                    ->modalHeading(fn(SuratMasuk $record) => $record->disposisis()->exists() ? 'Riwayat & Tambah Disposisi' : 'Input Disposisi')
                    ->schema([
                        Placeholder::make('existing_disposisis')
                            ->label('Riwayat Disposisi')
                            ->visible(fn(SuratMasuk $record) => $record->disposisis()->exists())
                            ->content(fn(SuratMasuk $record) => new HtmlString(
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
                        Section::make('Tambah Disposisi Baru')
                            ->schema([
                                Select::make('penerima_ids')
                                    ->label('Pilih Penerima')
                                    ->multiple()
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('sifat')
                                    ->options([
                                        'Biasa' => 'Biasa',
                                        'Segera' => 'Segera',
                                        'Penting' => 'Penting',
                                        'Rahasia' => 'Rahasia',
                                    ])
                                    ->default('Biasa')
                                    ->required(),
                                Textarea::make('catatan')
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

                        Notification::make()
                            ->title('Disposisi berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
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
            'index' => ListSuratMasuks::route('/'),
            'create' => CreateSuratMasuk::route('/create'),
            'edit' => EditSuratMasuk::route('/{record}/edit'),
        ];
    }
}
