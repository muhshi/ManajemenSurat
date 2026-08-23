<?php

namespace App\Filament\Resources\Sp2dRekapResource\Widgets;

use App\Models\Sp2dUpload;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;

class Sp2dUploadsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Import Data SP2D & Potongan';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sp2dUpload::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Import')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->state(function (Sp2dUpload $record) {
                        return $record->periode_bulan . '/' . $record->periode_tahun;
                    })
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_sp2d_terproses')
                    ->label('Total SP2D Terproses')
                    ->numeric(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'done',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengunggah'),
            ])
            ->actions([
                Tables\Actions\Action::make('download_sp2d')
                    ->label('SP2D')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Sp2dUpload $record) => $record->file_monitoring_sp2d ? Storage::url($record->file_monitoring_sp2d) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Sp2dUpload $record) => !empty($record->file_monitoring_sp2d)),
                Tables\Actions\Action::make('download_potongan')
                    ->label('Potongan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Sp2dUpload $record) => $record->file_potongan_spm ? Storage::url($record->file_potongan_spm) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Sp2dUpload $record) => !empty($record->file_potongan_spm)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
