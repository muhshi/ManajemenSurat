<?php

namespace App\Filament\Resources\SKS;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;
use App\Settings\SystemSettings;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use PhpOffice\PhpWord\TemplateProcessor;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use ZipArchive;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SKS\Pages\ListSurats;
use App\Filament\Resources\SKS\Pages\CreateSurat;
use App\Filament\Resources\SKS\Pages\EditSurat;
use App\Filament\Resources\SKResource\Pages;
use App\Models\Surat;
use App\Models\Klasifikasi;
use App\Services\TemplateService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SKResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Surat Keluar';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Surat Keputusan (SK)';

    protected static ?string $modelLabel = 'SK';

    protected static ?string $pluralModelLabel = 'SK';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_surat', 'SK');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nomor_surat')
                    ->label('Nomor SK')
                    ->readOnly()
                    ->default(fn() => Surat::generateNomorSurat(
                        (int) Surat::getNextNomorUrut(now()->year, 'SK'),
                        now()->year,
                        'SK'
                    ))
                    ->helperText('Format: XXX/{office_code}/KPA TAHUN {tahun}')
                    ->columnSpanFull(),

                Section::make('Generator Nomor SK')
                    ->description('Ubah komponen ini untuk menghasilkan nomor SK.')
                    ->schema([
                        Hidden::make('jenis_surat')
                            ->default('SK'),

                        TextInput::make('nomor_urut')
                            ->label('No. Urut')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(fn() => Surat::getNextNomorUrut(now()->year, 'SK'))
                            ->helperText('Nomor urut SK')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $tahun = $get('tahun') ?: now()->year;
                                $set('nomor_surat', Surat::generateNomorSurat((int) $state, (int) $tahun, 'SK'));
                            }),

                        Select::make('perihal')
                            ->label('Jenis Kegiatan (Placeholder)')
                            ->options([
                                'Peserta' => 'Pelatihan',
                                'Petugas' => 'Pelaksanaan',
                            ])
                            ->required()
                            ->helperText('Akan mengisi placeholder ${jenis_surat} di template SK')
                            ->native(false),

                        DatePicker::make('tanggal_surat')
                            ->label('Tanggal Penetapan')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $carbonDate = Carbon::parse($state);
                                $tahun = $carbonDate->year;
                                $set('tahun', $tahun);

                                $nomorUrut = $get('nomor_urut') ?: Surat::getNextNomorUrut($tahun, 'SK');
                                $set('nomor_urut', $nomorUrut);
                                $set('nomor_surat', Surat::generateNomorSurat((int) $nomorUrut, (int) $tahun, 'SK'));
                            }),

                        Hidden::make('tahun')
                            ->default(now()->year),
                    ])
                    ->columns(3),

                Section::make('Informasi SK')
                    ->description('Isi informasi SK yang akan digenerate')
                    ->schema([
                        TextInput::make('judul_surat')
                            ->label('Tentang (Judul SK)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Pembentukan Panitia Pelatihan Statistik')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Pejabat Penandatangan (Snapshot)')
                    ->description('Data ini tersimpan di surat dan tidak akan berubah meski pengaturan sistem diganti.')
                    ->schema([
                        TextInput::make('signer_city')
                            ->label('Kota Penetapan')
                            ->default(fn() => app(SystemSettings::class)->cert_city)
                            ->readOnly(),
                        TextInput::make('signer_name')
                            ->label('Nama Pejabat')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_name)
                            ->readOnly(),
                        TextInput::make('signer_nip')
                            ->label('NIP')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_nip)
                            ->readOnly(),
                        TextInput::make('signer_title')
                            ->label('Jabatan Pejabat')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_title)
                            ->readOnly()
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor SK')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(20)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState())
                    ->weight('bold'),

                TextColumn::make('judul_surat')
                    ->label('Tentang')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),

                TextColumn::make('perihal')
                    ->label('Jenis Kegiatan')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Peserta' => 'Pelatihan',
                        'Petugas' => 'Pelaksanaan',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state === 'Peserta' ? 'info' : 'success'),

                TextColumn::make('tanggal_surat')
                    ->label('Tgl. Penetapan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('signer_name')
                    ->label('Penandatangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = range($currentYear - 5, $currentYear + 1);
                        return array_combine($years, $years);
                    }),
                SelectFilter::make('perihal')
                    ->label('Jenis Kegiatan')
                    ->options([
                        'Peserta' => 'Pelatihan (Peserta)',
                        'Petugas' => 'Pelaksanaan (Petugas)',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('download')
                    ->label('Word')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (Surat $record) {
                        $settings = app(SystemSettings::class);

                        $templatePath = $settings->template_sk;

                        if (!$templatePath || !file_exists(storage_path('app/public/' . $templatePath))) {
                            Notification::make()
                                ->title('Template SK belum diupload di Pengaturan')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Initialize TemplateProcessor
                        $template = new TemplateProcessor(storage_path('app/public/' . $templatePath));

                        // Map variables
                        $template->setValue('nomor_surat', $record->nomor_surat);
                        $template->setValue('judul_surat', $record->judul_surat);
                        $template->setValue('tanggal_surat', Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
                        $template->setValue('tahun', $record->tahun);

                        // User specifically asked for this mapping for SK:
                        // Pelatihan (value => Peserta), Pelaksanaan (value => Petugas)
                        // In our form, we store "Peserta" or "Petugas" in 'perihal'
                        $template->setValue('jenis_surat', $record->perihal);

                        // Signer Snapshot
                        $template->setValue('nama_kepala', $record->signer_name);
                        $template->setValue('nip_kepala', $record->signer_nip);
                        $template->setValue('jabatan_kepala', $record->signer_title);
                        $template->setValue('kota_penetapan', $record->signer_city);

                        // Save to temp file
                        $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                        $fileName = "SK_{$safeFilename}.docx";
                        $tempPath = storage_path('app/' . $fileName); // Simpler path
                        $template->saveAs($tempPath);

                        return response()->download($tempPath, $fileName);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadBulk')
                        ->label('Download Semua (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $settings = app(SystemSettings::class);

                            // Create ZIP
                            $zipFileName = 'SK_Bulk_' . now()->format('YmdHis') . '.zip';
                            $zipPath = storage_path('app/' . $zipFileName);
                            $zip = new ZipArchive();

                            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                                Notification::make()
                                    ->title('Gagal membuat file ZIP')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $addedFiles = 0;
                            foreach ($records as $record) {
                                $templatePath = $settings->template_sk;

                                if (!$templatePath || !file_exists(storage_path('app/public/' . $templatePath))) {
                                    continue;
                                }

                                $template = new TemplateProcessor(storage_path('app/public/' . $templatePath));

                                // Map variables
                                $template->setValue('nomor_surat', $record->nomor_surat);
                                $template->setValue('judul_surat', $record->judul_surat);
                                $template->setValue('tanggal_surat', Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
                                $template->setValue('tahun', $record->tahun);
                                $template->setValue('jenis_surat', $record->perihal);

                                // Signer Snapshot
                                $template->setValue('nama_kepala', $record->signer_name);
                                $template->setValue('nip_kepala', $record->signer_nip);
                                $template->setValue('jabatan_kepala', $record->signer_title);
                                $template->setValue('kota_penetapan', $record->signer_city);

                                // Save to temp
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $fileName = "SK_{$safeFilename}.docx";
                                $tempPath = storage_path('app/temp_bulk_' . $fileName);
                                $template->saveAs($tempPath);

                                // Add to ZIP
                                $zip->addFile($tempPath, $fileName);
                                $addedFiles++;
                            }

                            $zip->close();

                            if ($addedFiles === 0) {
                                Notification::make()
                                    ->title('Tidak ada SK yang bisa didownload')
                                    ->warning()
                                    ->send();
                                if (file_exists($zipPath))
                                    unlink($zipPath);
                                return;
                            }

                            // Clean up
                            foreach ($records as $record) {
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $tempPath = storage_path('app/temp_bulk_SK_' . $safeFilename . '.docx');
                                if (file_exists($tempPath)) {
                                    unlink($tempPath);
                                }
                            }

                            // Clean up temp files
                            foreach ($records as $record) {
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $tempPathSingle = storage_path('app/temp_bulk_SK_' . $safeFilename . '.docx');
                                if (file_exists($tempPathSingle)) {
                                    unlink($tempPathSingle);
                                }
                            }

                            return response()->download($zipPath, $zipFileName);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListSurats::route('/'),
            'create' => CreateSurat::route('/create'),
            'edit' => EditSurat::route('/{record}/edit'),
        ];
    }
}
