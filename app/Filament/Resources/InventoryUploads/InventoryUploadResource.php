<?php

namespace App\Filament\Resources\InventoryUploads;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\InventoryUploads\Pages\ListInventoryUploads;
use App\Filament\Resources\InventoryUploads\Pages\CreateInventoryUpload;
use App\Exports\InventoryCardsExport;
use App\Filament\Resources\InventoryUploadResource\Pages;
use App\Filament\Resources\InventoryUploadResource\RelationManagers;
use App\Models\InventoryUpload;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class InventoryUploadResource extends Resource
{
    protected static ?string $model = InventoryUpload::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Upload Buku Persediaan';
    protected static string | \UnitEnum | null $navigationGroup = 'Inventaris';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Dokumen')
                    ->description('Pilih file PDF Rincian Buku Persediaan yang ingin diekstrak datanya.')
                    ->schema([
                        FileUpload::make('filename')
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
                TextColumn::make('filename')
                    ->label('File')
                    ->formatStateUsing(fn ($state) => basename($state))
                    ->searchable(),
                TextColumn::make('period_start')
                    ->label('Periode Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_end')
                    ->label('Periode Akhir')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('rows_extracted')
                    ->label('Total Data')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Diproses Pada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('export_kartu_kendali')
                    ->label('Export Kartu Kendali')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->schema([
                        Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $currentYear = (int) date('Y');
                                $years = [];
                                for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
                                    $years[$y] = $y;
                                }
                                return $years;
                            })
                            ->default((int) date('Y'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $year = $data['year'];
                        return Excel::download(
                            new InventoryCardsExport($year),
                            "kartu-kendali-persediaan-{$year}.xlsx"
                        );
                    }),
                Action::make('print')
                    ->label('Print Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (InventoryUpload $record) => route('inventory-upload.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListInventoryUploads::route('/'),
            'create' => CreateInventoryUpload::route('/create'),
        ];
    }
}
