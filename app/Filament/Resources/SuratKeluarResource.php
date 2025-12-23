<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Models\Surat;
use App\Models\Klasifikasi;
use App\Services\TemplateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SuratKeluarResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Surat Keluar & Lainnya';

    protected static ?string $modelLabel = 'Surat Keluar';

    protected static ?string $pluralModelLabel = 'Surat Keluar';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_surat', '!=', 'SK');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->readOnly()
                    ->default(fn() => Surat::generateNomorSurat(
                        (int) Surat::getNextNomorUrut(now()->year, 'Surat Keluar'),
                        now()->year,
                        'Surat Keluar',
                        'KP.650'
                    ))
                    ->helperText('Nomor surat akan di-generate otomatis berdasarkan field di bawah')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Generator Nomor Surat')
                    ->description('Pilih jenis surat dan klasifikasi untuk men-generate nomor.')
                    ->schema([
                        Forms\Components\Select::make('jenis_surat')
                            ->label('Jenis Surat')
                            ->options([
                                'Surat Keluar' => 'Surat Keluar',
                                'Memo' => 'Memo',
                                'Surat Pengantar' => 'Surat Pengantar',
                            ])
                            ->required()
                            ->default('Surat Keluar')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
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

                        Forms\Components\TextInput::make('memo_klasifikasi')
                            ->label('Klasifikasi')
                            ->default('KP.700')
                            ->readOnly()
                            ->visible(fn($get) => $get('jenis_surat') === 'Memo')
                            ->dehydrated(false),

                        Forms\Components\Select::make('klasifikasi_id')
                            ->label('Klasifikasi')
                            ->relationship('klasifikasi', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->nama}")
                            ->searchable()
                            ->preload()
                            ->required(fn($get) => $get('jenis_surat') === 'Surat Keluar')
                            ->visible(fn($get) => $get('jenis_surat') === 'Surat Keluar')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $nomorUrut = $get('nomor_urut') ?: 1;
                                $tahun = $get('tahun') ?: now()->year;
                                $jenis = $get('jenis_surat') ?: 'Surat Keluar';
                                $klasifikasi = Klasifikasi::find($state)?->kode ?: 'KP.650';
                                $set('nomor_surat', Surat::generateNomorSurat((int) $nomorUrut, (int) $tahun, $jenis, $klasifikasi));
                            }),

                        Forms\Components\TextInput::make('nomor_urut')
                            ->label('No. Urut')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(fn(Forms\Get $get) => Surat::getNextNomorUrut($get('tahun') ?: now()->year, $get('jenis_surat') ?: 'Surat Keluar'))
                            ->helperText('Nomor urut surat (bisa diubah manual)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $tahun = $get('tahun') ?: now()->year;
                                $jenis = $get('jenis_surat') ?: 'Surat Keluar';
                                $klasifikasi = 'KP.650';
                                if ($klasifikasiId = $get('klasifikasi_id')) {
                                    $klasifikasi = Klasifikasi::find($klasifikasiId)?->kode ?: 'KP.650';
                                }
                                $set('nomor_surat', Surat::generateNomorSurat((int) $state, (int) $tahun, $jenis, $klasifikasi));
                            }),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Informasi Surat')
                    ->description('Isi informasi surat yang akan digenerate')
                    ->schema([
                        Forms\Components\TextInput::make('kepada')
                            ->label('Kepada')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kepala BPS Provinsi Jawa Tengah'),

                        Forms\Components\TextInput::make('perihal')
                            ->label('Perihal / Deskripsi')
                            ->required(fn($get) => in_array($get('jenis_surat'), ['Surat Keluar', 'Memo']))
                            ->visible(fn($get) => in_array($get('jenis_surat'), ['Surat Keluar', 'Memo']))
                            ->maxLength(255)
                            ->placeholder('Contoh: Laporan Bulanan Desember'),

                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->label('Tanggal Surat')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $carbonDate = \Illuminate\Support\Carbon::parse($state);
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

                        Forms\Components\Hidden::make('tahun')
                            ->default(now()->year),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pejabat Penandatangan (Snapshot)')
                    ->description('Data ini tersimpan di surat dan tidak akan berubah meski pengaturan sistem diganti.')
                    ->schema([
                        Forms\Components\TextInput::make('signer_city')
                            ->label('Kota Penetapan')
                            ->default(fn() => app(\App\Settings\SystemSettings::class)->cert_city)
                            ->readOnly(),
                        Forms\Components\TextInput::make('signer_name')
                            ->label('Nama Pejabat')
                            ->default(fn() => app(\App\Settings\SystemSettings::class)->cert_signer_name)
                            ->readOnly(),
                        Forms\Components\TextInput::make('signer_nip')
                            ->label('NIP')
                            ->default(fn() => app(\App\Settings\SystemSettings::class)->cert_signer_nip)
                            ->readOnly(),
                        Forms\Components\TextInput::make('signer_title')
                            ->label('Jabatan Pejabat')
                            ->default(fn() => app(\App\Settings\SystemSettings::class)->cert_signer_title)
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
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kepada')
                    ->label('Kepada')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal / Deskripsi')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('jenis_surat')
                    ->label('Jenis Surat')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('signer_name')
                    ->label('Penandatangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = range($currentYear - 5, $currentYear + 1);
                        return array_combine($years, $years);
                    }),
                Tables\Filters\SelectFilter::make('jenis_surat')
                    ->options([
                        'Surat Keluar' => 'Surat Keluar',
                        'Memo' => 'Memo',
                        'Surat Pengantar' => 'Surat Pengantar',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Word')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (Surat $record) {
                        $settings = app(\App\Settings\SystemSettings::class);

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
                        $template = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/public/' . $templatePath));

                        // Map variables
                        $template->setValue('nomor_surat', $record->nomor_surat);
                        $template->setValue('kepada', $record->kepada);
                        $template->setValue('perihal', $record->perihal);
                        $template->setValue('tanggal_surat', \Illuminate\Support\Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('downloadBulk')
                        ->label('Download Semua (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $settings = app(\App\Settings\SystemSettings::class);

                            // Create ZIP
                            $zipFileName = 'Surat_Bulk_' . now()->format('YmdHis') . '.zip';
                            $zipPath = storage_path('app/' . $zipFileName);
                            $zip = new \ZipArchive();

                            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
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

                                $template = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/public/' . $templatePath));

                                // Map variables
                                $template->setValue('nomor_surat', $record->nomor_surat);
                                $template->setValue('kepada', $record->kepada);
                                $template->setValue('perihal', $record->perihal);
                                $template->setValue('tanggal_surat', \Illuminate\Support\Carbon::parse($record->tanggal_surat)->translatedFormat('d F Y'));
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
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSurats::route('/'),
            'create' => Pages\CreateSurat::route('/create'),
            'edit' => Pages\EditSurat::route('/{record}/edit'),
        ];
    }
}
