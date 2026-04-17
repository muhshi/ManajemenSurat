<?php

namespace App\Filament\Resources\Sp2dRekapResource\Widgets;

use App\Models\Sp2dUpload;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;

class Sp2dUploadsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Riwayat File Upload SP2D';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sp2dUpload::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('filename')
                    ->label('Nama File')
                    ->formatStateUsing(fn(string $state) => basename($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengunggah'),
            ])
            ->actions([
                \Filament\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Sp2dUpload $record) => $record->filename ? Storage::url($record->filename) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Sp2dUpload $record) => !empty($record->filename)),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
