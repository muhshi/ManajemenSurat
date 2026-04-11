<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryUploadResource\Pages;
use App\Filament\Resources\InventoryUploadResource\RelationManagers;
use App\Models\InventoryUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryUploadResource extends Resource
{
    protected static ?string $model = InventoryUpload::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Upload Buku Persediaan';
    protected static ?string $navigationGroup = 'Inventaris';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload Dokumen')
                    ->description('Pilih file PDF Rincian Buku Persediaan yang ingin diekstrak datanya.')
                    ->schema([
                        Forms\Components\FileUpload::make('filename')
                            ->label('File PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('inventory-uploads')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->label('File')
                    ->formatStateUsing(fn ($state) => basename($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Periode Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Periode Akhir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rows_extracted')
                    ->label('Total Data')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Diproses Pada')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (InventoryUpload $record) => route('inventory-upload.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListInventoryUploads::route('/'),
            'create' => Pages\CreateInventoryUpload::route('/create'),
        ];
    }
}
