<?php

namespace App\Filament\Resources\RuanganResource\RelationManagers;

use App\Models\Bmn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * BMN yang berlokasi (ruangan_id) di ruangan ini.
 */
class BmnsRelationManager extends RelationManager
{
    protected static string $relationship = 'bmns';

    protected static ?string $title = 'Aset di Ruangan Ini';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);   // Edit via BmnResource
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_barang')
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->description(fn ($record) => 'NUP: ' . $record->nup)
                    ->searchable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->nama_barang),

                TextColumn::make('jenis_bmn')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'TANAH'                      => 'primary',
                        'MESIN PERALATAN KHUSUS TIK' => 'info',
                        'ALAT ANGKUTAN BERMOTOR'     => 'warning',
                        default                      => 'gray',
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

                TextColumn::make('nilai_buku')
                    ->label('Nilai Buku')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                IconColumn::make('henti_guna')
                    ->label('Henti Guna')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('kondisi')
                    ->options([
                        'Baik'         => 'Baik',
                        'Rusak Ringan' => 'Rusak Ringan',
                        'Rusak Berat'  => 'Rusak Berat',
                    ]),
            ])
            ->headerActions([
                // Attach aset yang belum punya ruangan atau pindahin dari ruangan lain
                Action::make('attach_bmn')
                    ->label('Tambahkan Aset ke Ruangan Ini')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Select::make('bmn_ids')
                            ->label('Pilih Aset BMN')
                            ->options(
                                fn () => Bmn::whereNull('ruangan_id')
                                    ->orWhere('ruangan_id', '!=', $this->getOwnerRecord()->id)
                                    ->orderBy('kode_barang')
                                    ->get()
                                    ->mapWithKeys(fn ($b) => [
                                        $b->id => $b->kode_barang . '/' . $b->nup . ' – ' . $b->nama_barang,
                                    ])
                            )
                            ->multiple()
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        Bmn::whereIn('id', $data['bmn_ids'])
                            ->update(['ruangan_id' => $this->getOwnerRecord()->id]);

                        Notification::make()
                            ->title(count($data['bmn_ids']) . ' aset berhasil ditambahkan ke ruangan ini')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('lepas_dari_ruangan')
                    ->label('Lepas dari Ruangan')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['ruangan_id' => null]);
                        Notification::make()
                            ->title('Aset dilepas dari ruangan')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('kode_barang')
            ->striped();
    }
}
