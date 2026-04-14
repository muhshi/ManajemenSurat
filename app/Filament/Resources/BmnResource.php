<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BmnResource\Pages\CreateBmn;
use App\Filament\Resources\BmnResource\Pages\EditBmn;
use App\Filament\Resources\BmnResource\Pages\ListBmns;
use App\Models\Bmn;
use App\Models\Pegawai;
use App\Models\Ruangan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BmnResource extends Resource
{
    protected static ?string $model = Bmn::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen BMN';

    protected static ?string $navigationLabel = 'Data Aset BMN';

    protected static ?string $modelLabel = 'BMN';

    protected static ?string $pluralModelLabel = 'Data Aset BMN';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        // ── Tab 1: Identitas Barang ──────────────────────────────
                        Tab::make('Identitas Barang')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('kode_barang')
                                        ->label('Kode Barang')
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('nup')
                                        ->label('NUP')
                                        ->numeric()
                                        ->required(),
                                ]),

                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    Select::make('jenis_bmn')
                                        ->label('Jenis BMN')
                                        ->options([
                                            'TANAH'                      => 'Tanah',
                                            'ALAT BESAR'                 => 'Alat Besar',
                                            'ALAT ANGKUTAN BERMOTOR'     => 'Alat Angkutan Bermotor',
                                            'BANGUNAN DAN GEDUNG'        => 'Bangunan dan Gedung',
                                            'MESIN PERALATAN NON TIK'    => 'Mesin Peralatan Non TIK',
                                            'MESIN PERALATAN KHUSUS TIK' => 'Mesin Peralatan Khusus TIK',
                                            'ASET TETAP LAINNYA'         => 'Aset Tetap Lainnya',
                                            'ASET TAK BERWUJUD'          => 'Aset Tak Berwujud',
                                            'RUMAH NEGARA'               => 'Rumah Negara',
                                        ])
                                        ->searchable()
                                        ->live(),

                                    Select::make('kondisi')
                                        ->label('Kondisi')
                                        ->options([
                                            'Baik'         => 'Baik',
                                            'Rusak Ringan' => 'Rusak Ringan',
                                            'Rusak Berat'  => 'Rusak Berat',
                                        ])
                                        ->required(),
                                ]),

                                Grid::make(2)->schema([
                                    TextInput::make('merk')->label('Merk'),
                                    TextInput::make('tipe')->label('Tipe'),
                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('umur_aset')
                                        ->label('Umur Aset (Tahun)')
                                        ->numeric(),

                                    TextInput::make('no_polisi')
                                        ->label('No. Polisi')
                                        ->visible(fn (Get $get) => $get('jenis_bmn') === 'ALAT ANGKUTAN BERMOTOR'),

                                    TextInput::make('no_dokumen')
                                        ->label('No. Dokumen'),
                                ]),

                                TextInput::make('kode_register')
                                    ->label('Kode Register (SIMAN)')
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->columnSpanFull(),
                            ]),

                        // ── Tab 2: Lokasi & Penanggung Jawab ─────────────────────
                        Tab::make('Lokasi & Penanggung Jawab')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Select::make('ruangan_id')
                                    ->label('Lokasi Ruangan')
                                    ->options(fn () => Ruangan::all()->mapWithKeys(fn ($r) => [$r->id => $r->kode_ruang . ' - ' . $r->nama_ruang]))
                                    ->searchable()
                                    ->columnSpanFull(),

                                Forms\Components\Radio::make('penanggung_jawab_type_radio')
                                    ->label('Tipe Penanggung Jawab')
                                    ->options([
                                        'pegawai' => 'Pegawai',
                                        'ruangan' => 'Tim / Ruangan',
                                        'none'    => 'Tidak Ada',
                                    ])
                                    ->default('none')
                                    ->live()
                                    ->afterStateHydrated(function (Forms\Components\Radio $component, $state, $record) {
                                        if ($record && $record->penanggung_jawab_type) {
                                            $component->state(
                                                str_contains($record->penanggung_jawab_type, 'Pegawai') ? 'pegawai' : 'ruangan'
                                            );
                                        }
                                    })
                                    ->columnSpanFull(),

                                Select::make('penanggung_jawab_id_pegawai')
                                    ->label('Penanggung Jawab - Pegawai')
                                    ->options(fn () => Pegawai::where('aktif', true)->pluck('nama', 'id'))
                                    ->searchable()
                                    ->visible(fn (Get $get) => $get('penanggung_jawab_type_radio') === 'pegawai')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->penanggung_jawab_type === Pegawai::class) {
                                            $component->state($record->penanggung_jawab_id);
                                        }
                                    })
                                    ->columnSpanFull(),

                                Select::make('penanggung_jawab_id_ruangan')
                                    ->label('Penanggung Jawab - Ruangan / Tim')
                                    ->options(fn () => Ruangan::all()->pluck('nama_ruang', 'id'))
                                    ->searchable()
                                    ->visible(fn (Get $get) => $get('penanggung_jawab_type_radio') === 'ruangan')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->penanggung_jawab_type === Ruangan::class) {
                                            $component->state($record->penanggung_jawab_id);
                                        }
                                    })
                                    ->columnSpanFull(),
                            ]),

                        // ── Tab 3: Nilai Aset ─────────────────────────────────────
                        Tab::make('Nilai Aset')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('nilai_perolehan')
                                        ->label('Nilai Perolehan (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $set('nilai_buku', max(0, (float)($get('nilai_perolehan') ?? 0) - (float)($get('nilai_penyusutan') ?? 0)));
                                        }),

                                    TextInput::make('nilai_penyusutan')
                                        ->label('Nilai Penyusutan (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $set('nilai_buku', max(0, (float)($get('nilai_perolehan') ?? 0) - (float)($get('nilai_penyusutan') ?? 0)));
                                        }),
                                ]),

                                TextInput::make('nilai_buku')
                                    ->label('Nilai Buku (Rp) — Auto Hitung')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    DatePicker::make('tanggal_perolehan')
                                        ->label('Tanggal Perolehan')
                                        ->displayFormat('d/m/Y'),

                                    Select::make('status_penggunaan')
                                        ->label('Status Penggunaan')
                                        ->options([
                                            'Digunakan sendiri untuk operasional'  => 'Digunakan sendiri untuk operasional',
                                            'Digunakan sendiri untuk dinas jabatan' => 'Digunakan sendiri untuk dinas jabatan',
                                            'Digunakan pihak lain'                 => 'Digunakan pihak lain',
                                            'Tidak digunakan'                      => 'Tidak digunakan',
                                        ]),
                                ]),

                                Select::make('intra_extra')
                                    ->label('Intra / Ekstra')
                                    ->options(['Intra' => 'Intra', 'Ekstra' => 'Ekstra'])
                                    ->default('Intra'),
                            ]),

                        // ── Tab 4: Status & Flags ─────────────────────────────────
                        Tab::make('Status & Flags')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Grid::make(2)->schema([
                                    Toggle::make('henti_guna')
                                        ->label('Henti Guna')
                                        ->helperText('Tandai jika aset sudah tidak digunakan'),

                                    Toggle::make('usul_hapus')
                                        ->label('Usul Hapus')
                                        ->helperText('Tandai jika aset diusulkan untuk dihapus'),
                                ]),

                                Textarea::make('catatan')
                                    ->label('Catatan')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                FileUpload::make('foto')
                                    ->label('Foto Kondisi Fisik')
                                    ->multiple()
                                    ->image()
                                    ->directory('bmn-foto')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->description(fn ($record) => 'NUP: ' . $record->nup)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->nama_barang)
                    ->wrap(),

                TextColumn::make('jenis_bmn')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'TANAH'                      => 'primary',
                        'MESIN PERALATAN KHUSUS TIK' => 'info',
                        'ALAT ANGKUTAN BERMOTOR'     => 'warning',
                        'BANGUNAN DAN GEDUNG', 'RUMAH NEGARA' => 'danger',
                        default                              => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'TANAH'                      => 'Tanah',
                        'ALAT BESAR'                 => 'Alat Besar',
                        'ALAT ANGKUTAN BERMOTOR'     => 'Alat Angkutan',
                        'BANGUNAN DAN GEDUNG'        => 'Bangunan',
                        'MESIN PERALATAN NON TIK'    => 'Mesin Non TIK',
                        'MESIN PERALATAN KHUSUS TIK' => 'Mesin TIK',
                        'ASET TETAP LAINNYA'         => 'Aset Lainnya',
                        'ASET TAK BERWUJUD'          => 'Tak Berwujud',
                        'RUMAH NEGARA'               => 'Rumah Negara',
                        default                      => $state,
                    }),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Baik'         => 'success',
                        'Rusak Ringan' => 'warning',
                        'Rusak Berat'  => 'danger',
                        default        => 'gray',
                    }),

                TextColumn::make('ruangan.nama_ruang')
                    ->label('Ruangan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum berlokasi')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('penanggung_jawab_nama')
                    ->label('Penanggung Jawab')
                    ->getStateUsing(function ($record) {
                        if (! $record->penanggung_jawab_type) {
                            return '–';
                        }
                        $pj = $record->penanggungJawab;
                        if (! $pj) {
                            return '–';
                        }
                        return $pj instanceof Pegawai ? $pj->nama : $pj->nama_ruang;
                    })
                    ->placeholder('–'),

                TextColumn::make('nilai_buku')
                    ->label('Nilai Buku')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                IconColumn::make('henti_guna')
                    ->label('Henti Guna')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('jenis_bmn')
                    ->label('Jenis BMN')
                    ->options([
                        'TANAH'                      => 'Tanah',
                        'ALAT BESAR'                 => 'Alat Besar',
                        'ALAT ANGKUTAN BERMOTOR'     => 'Alat Angkutan Bermotor',
                        'BANGUNAN DAN GEDUNG'        => 'Bangunan dan Gedung',
                        'MESIN PERALATAN NON TIK'    => 'Mesin Peralatan Non TIK',
                        'MESIN PERALATAN KHUSUS TIK' => 'Mesin Peralatan Khusus TIK',
                        'ASET TETAP LAINNYA'         => 'Aset Tetap Lainnya',
                        'ASET TAK BERWUJUD'          => 'Aset Tak Berwujud',
                        'RUMAH NEGARA'               => 'Rumah Negara',
                    ]),

                SelectFilter::make('kondisi')
                    ->label('Kondisi')
                    ->options([
                        'Baik'         => 'Baik',
                        'Rusak Ringan' => 'Rusak Ringan',
                        'Rusak Berat'  => 'Rusak Berat',
                    ]),

                SelectFilter::make('ruangan_id')
                    ->label('Ruangan')
                    ->options(fn () => Ruangan::all()->mapWithKeys(fn ($r) => [$r->id => $r->kode_ruang . ' - ' . $r->nama_ruang])),

                TernaryFilter::make('henti_guna')->label('Henti Guna'),
                TernaryFilter::make('usul_hapus')->label('Usul Hapus'),
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
            ->defaultSort('kode_barang')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBmns::route('/'),
            'create' => CreateBmn::route('/create'),
            'edit'   => EditBmn::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count() ?: null;
    }
}
