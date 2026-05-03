<?php

namespace App\Filament\Resources\Agendas;

use App\Filament\Resources\Agendas\Pages;
use App\Filament\Resources\Agendas\RelationManagers;
use App\Models\Agenda;
use App\Services\AgendaDocService;
use App\Settings\SystemSettings;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AgendaResource extends Resource
{
    protected static ?string $model = Agenda::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'Notulensi Rapat';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Agenda Rapat';
    protected static ?string $modelLabel = 'Agenda';
    protected static ?string $pluralModelLabel = 'Agenda Rapat';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Agenda')
                    ->description('Detail jadwal dan lokasi rapat')
                    ->schema([
                        Group::make([
                            TextInput::make('nomor_urut')
                                ->label('Nomor Urut')
                                ->numeric()
                                ->required()
                                ->live()
                                ->default(fn() => Agenda::getNextUrut(now()->year))
                                ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                    $tanggal = $get('tanggal_rapat');
                                    if ($tanggal && $state) {
                                        $set('nomor_surat', Agenda::formatNomor((int) $state, new \DateTime($tanggal)));
                                    }
                                }),
                            TextInput::make('nomor_surat')
                                ->label('Nomor Surat')
                                ->readOnly()
                                ->default(fn() => Agenda::generateNomor(now()->year)),
                        ])->columns(2),

                        TextInput::make('judul')
                            ->label('Judul Rapat')
                            ->required()
                            ->placeholder('Contoh: Rapat Pembahasan Mutasi Pegawai'),
                        TextInput::make('perihal')
                            ->label('Perihal Undangan')
                            ->required()
                            ->placeholder('Contoh: Undangan Rapat Struktural'),
                        TextInput::make('penerima_undangan')
                            ->label('Kepada Yth.')
                            ->required()
                            ->placeholder('Contoh: Seluruh Ketua Tim'),
                        TextInput::make('tempat')
                            ->label('Tempat')
                            ->required(),
                        DatePicker::make('tanggal_rapat')
                            ->label('Tanggal Rapat')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                if ($state) {
                                    $year = (int) date('Y', strtotime($state));
                                    $set('nomor_urut', Agenda::getNextUrut($year));
                                    $set('nomor_surat', Agenda::formatNomor(Agenda::getNextUrut($year), new \DateTime($state)));
                                }
                            }),
                        TimePicker::make('waktu_mulai')
                            ->label('Waktu Mulai')
                            ->required(),
                        TimePicker::make('waktu_selesai')
                            ->label('Waktu Selesai')
                            ->helperText('Kosongkan jika "sampai selesai"'),
                        TextInput::make('pimpinan_rapat')
                            ->label('Pimpinan Rapat')
                            ->required(),
                        TextInput::make('narasumber')
                            ->label('Narasumber'),
                        TextInput::make('notulis')
                            ->label('Notulis')
                            ->default(fn() => auth()->user() ? auth()->user()->name : ''),
                        TextInput::make('peserta_rapat')
                            ->label('Peserta Rapat (Keterangan)')
                            ->placeholder('Contoh: Ketua Tim, Kepala, Kasubag dll'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(1),

                Section::make('Penandatangan (Snapshot)')
                    ->description('Pejabat yang menandatangani undangan')
                    ->schema([
                        TextInput::make('signer_name')
                            ->label('Nama Pejabat')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_name)
                            ->readOnly(),
                        TextInput::make('signer_nip')
                            ->label('NIP')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_nip)
                            ->readOnly(),
                        TextInput::make('signer_title')
                            ->label('Jabatan')
                            ->default(fn() => app(SystemSettings::class)->cert_signer_title)
                            ->readOnly()
                            ->columnSpanFull(),
                        TextInput::make('signer_city')
                            ->label('Kota')
                            ->default(fn() => app(SystemSettings::class)->cert_city)
                            ->readOnly(),
                    ])
                    ->collapsed()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('judul')
                    ->label('Judul Rapat')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('tanggal_rapat')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('pimpinan_rapat')
                    ->label('Pimpinan'),
                TextColumn::make('peserta_count')
                    ->label('Peserta')
                    ->counts('peserta')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('notulensi_status')
                    ->label('Notulensi')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->isi_notulensi))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->color(fn($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('isi_notulensi')
                    ->label(fn($record) => empty($record->isi_notulensi) ? 'Isi Notulensi' : 'Edit Notulensi')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color(fn($record) => empty($record->isi_notulensi) ? 'warning' : 'info')
                    ->form([
                        Textarea::make('isi_notulensi')
                            ->label('Hasil Pembahasan')
                            ->rows(8)
                            ->required(),
                        Textarea::make('keputusan')
                            ->label('Keputusan')
                            ->rows(4),
                        Textarea::make('tindak_lanjut')
                            ->label('Tindak Lanjut')
                            ->rows(4),
                    ])
                    ->action(function (Agenda $record, array $data) {
                        $record->update($data);
                        Notification::make()
                            ->title('Notulensi berhasil disimpan')
                            ->success()
                            ->send();
                    }),
                Action::make('download')
                    ->label('Download .docx')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (Agenda $record) {
                        try {
                            $path = app(AgendaDocService::class)->generate($record);
                            return response()->download($path);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PesertaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendas::route('/'),
            'create' => Pages\CreateAgenda::route('/create'),
            'edit' => Pages\EditAgenda::route('/{record}/edit'),
        ];
    }
}
