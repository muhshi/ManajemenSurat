<?php

namespace App\Filament\Widgets;

use App\Models\Bmn;
use App\Models\Ruangan;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BmnPerRuanganTable extends BaseWidget
{
    protected static ?string $heading = 'Top Ruangan Berdasarkan Jumlah Aset';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ruangan::query()->withCount('bmns')->orderByDesc('bmns_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_ruang')
                    ->label('Kode')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_ruang')
                    ->label('Nama Ruang'),

                Tables\Columns\TextColumn::make('lantai')
                    ->label('Lantai')
                    ->formatStateUsing(fn ($state) => 'Lt. ' . $state),

                Tables\Columns\TextColumn::make('bmns_count')
                    ->label('Jumlah Aset')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
