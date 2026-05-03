<?php

namespace App\Filament\Resources\SuratKeluars;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Hidden;
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
use App\Filament\Resources\SuratKeluars\Pages\ListSurats;
use App\Filament\Resources\SuratKeluars\Pages\CreateSurat;
use App\Filament\Resources\SuratKeluars\Pages\EditSurat;
use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Models\Surat;
use App\Models\Klasifikasi;
use App\Services\TemplateService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SuratKeluarResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static string | \UnitEnum | null $navigationGroup = 'Surat Keluar';
    protected static ?int $navigationSort = 21;
    protected static ?string $navigationLabel = 'Surat Keluar & Lainnya';

    protected static ?string $modelLabel = 'Surat Keluar';

    protected static ?string $pluralModelLabel = 'Surat Keluar';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_surat', '!=', 'SK');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Surat Keluar')
                    ->description('Isi informasi surat untuk mendapatkan nomor urut dan generate dokumen')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Fieldset::make('Generator Nomor Surat')
                            ->schema([
                                Group::make([
                                    Select::make('jenis_surat')
                                        ->label('Jenis Surat')
                                        ->prefixIcon('heroicon-m-document-text')
                                        ->options([
                                            'Surat Keluar' => 'Surat Keluar',
                                            'Memo' => 'Memo',
                                            'Surat Pengantar' => 'Surat Pengantar',
                                        ])
                                        ->required()
                                        ->default('Surat Keluar')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state) {
                                    return;
                                }
                                $tahun = $get('tahun') ?: now()->year;
                                $nextUrut = Surat::getNextNomorUrut($tahun, $state);
                                $set('nomor_urut', $nextUrut);

                                if ($state === 'Memo') {
                                    $set('klasifikasi_id', 113); // Default for Memo
                                } else {
                                    $set('klasifikasi_id', null);
                                }

                                $klasifikasi = 'KP.650';
                                if ($klasifikasiId = $get('klasifikasi_id')) {
                                    $klasifikasiRecord = Klasifikasi::find($klasifikasiId);
                                    $klasifikasi = $klasifikasiRecord?->kode ?: 'KP.650';
                                }
                                $set('nomor_surat', Surat::generateNomorSurat((int) $nextUrut, (int) $tahun, $state, $klasifikasi));
                            }),

                                    TextInput::make('memo_klasifikasi')
                                        ->label('Klasifikasi')
                                        ->prefixIcon('heroicon-m-tag')
                                        ->default('KP.700')
                                        ->readOnly()
                                        ->visible(fn($get) => $get('jenis_surat') === 'Memo')
                                        ->dehydrated(false),

                                    Select::make('klasifikasi_id')
                                        ->label('Klasifikasi')
                                        ->prefixIcon('heroicon-m-tag')
                                        ->relationship('klasifikasi', 'nama')
                                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->nama}")
                                        ->searchable()
                                        ->preload()
                                        ->required(fn($get) => $get('jenis_surat') === 'Surat Keluar')
                                        ->visible(fn($get) => $get('jenis_surat') === 'Surat Keluar')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $nomorUrut = $get('nomor_urut') ?: 1;
                                            $tahun = $get('tahun') ?: now()->year;
                                            $jenis = $get('jenis_surat') ?: 'Surat Keluar';
                                            $klasifikasi = Klasifikasi::find($state)?->kode ?: 'KP.650';
                                            $set('nomor_surat', Surat::generateNomorSurat((int) $nomorUrut, (int) $tahun, $jenis, $klasifikasi));
                                        }),

                                    TextInput::make('nomor_urut')
                                        ->label('No. Urut')
                                        ->prefixIcon('heroicon-m-hashtag')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(fn(Get $get) => Surat::getNextNomorUrut($get('tahun') ?: now()->year, $get('jenis_surat') ?: 'Surat Keluar'))
                                        ->helperText('Bisa diubah manual')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $tahun = $get('tahun') ?: now()->year;
                                            $jenis = $get('jenis_surat') ?: 'Surat Keluar';
                                            $klasifikasi = 'KP.650';
                                            if ($klasifikasiId = $get('klasifikasi_id')) {
                                                $klasifikasi = Klasifikasi::find($klasifikasiId)?->kode ?: 'KP.650';
                                            }
                                            $set('nomor_surat', Surat::generateNomorSurat((int) $state, (int) $tahun, $jenis, $klasifikasi));
                                        }),
                                ])->columns(3)->columnSpanFull(),

                                TextInput::make('nomor_surat')
                                    ->label('Nomor Surat')
                                    ->prefixIcon('heroicon-m-document-text')
                                    ->readOnly()
                                    ->default(fn() => Surat::generateNomorSurat(
                                        (int) Surat::getNextNomorUrut(now()->year, 'Surat Keluar'),
                                        now()->year,
                                        'Surat Keluar',
                                        'KP.650'
                                    ))
                                    ->helperText('Otomatis di-generate')
                                    ->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),

                        Fieldset::make('Informasi Surat')
                            ->schema([
                                Group::make([
                                    TextInput::make('kepada')
                                        ->label('Kepada')
                                        ->prefixIcon('heroicon-m-user')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Contoh: Kepala BPS Provinsi Jawa Tengah'),

                                    DatePicker::make('tanggal_surat')
                                        ->label('Tanggal Surat')
                                        ->prefixIcon('heroicon-m-calendar')
                                        ->required()
                                        ->default(now())
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $carbonDate = Carbon::parse($state);
                                $tahun = $carbonDate->year;
                                $set('tahun', $tahun);

                                $jenis = $get('jenis_surat') ?: 'Surat Keluar';
                                $nomorUrut = Surat::getNextNomorUrut($tahun, $jenis);
                                $set('nomor_urut', $nomorUrut);

                                $klasifikasi = 'KP.650';
                                if ($klasifikasiId = $get('klasifikasi_id')) {
                                    $klasifikasi = Klasifikasi::find($klasifikasiId)?->kode ?: 'KP.650';
                                }
                                $set('nomor_surat', Surat::generateNomorSurat((int) $nomorUrut, (int) $tahun, $jenis, $klasifikasi));
                            }),

                                TextInput::make('perihal')
                                    ->label('Perihal / Deskripsi')
                                    ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                                    ->required(fn($get) => in_array($get('jenis_surat'), ['Surat Keluar', 'Memo']))
                                    ->visible(fn($get) => in_array($get('jenis_surat'), ['Surat Keluar', 'Memo']))
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Laporan Bulanan Desember')
                                    ->columnSpanFull(),

                                Hidden::make('tahun')
                                    ->default(now()->year),
                            ])->columns(2)->columnSpanFull(),
                    ])->columnSpanFull(),
                ])->columnSpanFull(),

                Section::make('Pejabat Penandatangan (Snapshot)')
                    ->description('Data ini tersimpan di surat dan tidak akan berubah meski pengaturan sistem diganti.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        TextInput::make('signer_city')
                            ->label('Kota Penetapan')
                            ->prefixIcon('heroicon-m-building-office-2')
                            ->default(fn() => app(SystemSettings::class)->cert_city)
                            ->readOnly(),
                        TextInput::make('signer_name')
                            ->label('Nama Pejabat')
                            ->prefixIcon('heroicon-m-user')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_name)
                            ->readOnly(),
                        TextInput::make('signer_nip')
                            ->label('NIP')
                            ->prefixIcon('heroicon-m-identification')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_nip)
                            ->readOnly(),
                        TextInput::make('signer_title')
                            ->label('Jabatan Pejabat')
                            ->prefixIcon('heroicon-m-briefcase')
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
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->limit(20)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),

                TextColumn::make('kepada')
                    ->label('Kepada')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),

                TextColumn::make('perihal')
                    ->label('Perihal / Deskripsi')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn(TextColumn $column): ?string => $column->getState()),

                TextColumn::make('jenis_surat')
                    ->label('Jenis Surat')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('tanggal_surat')
                    ->label('Tanggal')
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

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = range($currentYear - 5, $currentYear + 1);
                        return array_combine($years, $years);
                    }),
                SelectFilter::make('jenis_surat')
                    ->options([
                        'Surat Keluar' => 'Surat Keluar',
                        'Memo' => 'Memo',
                        'Surat Pengantar' => 'Surat Pengantar',
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

                        // Select template based on type
                        $templatePath = match ($record->jenis_surat) {
                            'Surat Keluar' => $settings->template_surat_keluar,
                            'Memo' => $settings->template_memo,
                            'Surat Pengantar' => $settings->template_surat_pengantar,
                            default => null,
                        };

                        if (!$templatePath || !file_exists(storage_path('app/public/' . $templatePath))) {
                            Notification::make()
                                ->title('Template untuk jenis ini belum diupload di Pengaturan')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Initialize TemplateProcessor
                        $template = new TemplateProcessor(storage_path('app/public/' . $templatePath));

                        // Map variables
                        $template->setValue('nomor_surat', $record->nomor_surat);
                        $template->setValue('kepada', $record->kepada);
                        $template->setValue('perihal', $record->perihal);
                        $template->setValue('tanggal_surat', Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
                        $template->setValue('tahun', $record->tahun);
                        $template->setValue('jenis_surat', $record->jenis_surat);

                        // Signer Snapshot
                        $template->setValue('nama_kepala', $record->signer_name);
                        $template->setValue('nip_kepala', $record->signer_nip);
                        $template->setValue('jabatan_kepala', $record->signer_title);
                        $template->setValue('kota_penetapan', $record->signer_city);

                        // Save to temp file
                        $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                        $fileName = "Surat_{$safeFilename}.docx";
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
                            $zipFileName = 'Surat_Bulk_' . now()->format('YmdHis') . '.zip';
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
                                $templatePath = match ($record->jenis_surat) {
                                    'Surat Keluar' => $settings->template_surat_keluar,
                                    'Memo' => $settings->template_memo,
                                    'Surat Pengantar' => $settings->template_surat_pengantar,
                                    default => null,
                                };

                                if (!$templatePath || !file_exists(storage_path('app/public/' . $templatePath))) {
                                    continue;
                                }

                                $template = new TemplateProcessor(storage_path('app/public/' . $templatePath));

                                // Map variables
                                $template->setValue('nomor_surat', $record->nomor_surat);
                                $template->setValue('kepada', $record->kepada);
                                $template->setValue('perihal', $record->perihal);
                                $template->setValue('tanggal_surat', Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
                                $template->setValue('tahun', $record->tahun);
                                $template->setValue('jenis_surat', $record->jenis_surat);

                                // Signer Snapshot
                                $template->setValue('nama_kepala', $record->signer_name);
                                $template->setValue('nip_kepala', $record->signer_nip);
                                $template->setValue('jabatan_kepala', $record->signer_title);
                                $template->setValue('kota_penetapan', $record->signer_city);

                                // Save to temp
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $fileName = "Surat_{$safeFilename}.docx";
                                $tempPath = storage_path('app/temp_bulk_' . $fileName);
                                $template->saveAs($tempPath);

                                // Add to ZIP
                                $zip->addFile($tempPath, $fileName);
                                $addedFiles++;
                            }

                            $zip->close();

                            if ($addedFiles === 0) {
                                Notification::make()
                                    ->title('Tidak ada surat yang bisa didownload (Template tidak ditemukan)')
                                    ->warning()
                                    ->send();
                                if (file_exists($zipPath))
                                    unlink($zipPath);
                                return;
                            }

                            // Clean up temp files
                            foreach ($records as $record) {
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $tempPath = storage_path('app/temp_bulk_Surat_' . $safeFilename . '.docx');
                                if (file_exists($tempPath)) {
                                    unlink($tempPath);
                                }
                            }

                            // Clean up temp files
                            foreach ($records as $record) {
                                $safeFilename = str_replace(['/', '\\'], '_', $record->nomor_surat);
                                $tempPathSingle = storage_path('app/temp_bulk_Surat_' . $safeFilename . '.docx');
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
