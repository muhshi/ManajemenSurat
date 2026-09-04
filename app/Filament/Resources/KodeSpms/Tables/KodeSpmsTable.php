<?php

namespace App\Filament\Resources\KodeSpms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KodeSpmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode SPM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama SPM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jalur')
                    ->label('Jalur Verifikasi')
                    ->badge()
                    ->colors([
                        'success' => '1_pihak',
                        'warning' => 'banyak_pihak',
                        'danger' => 'gup',
                    ])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
