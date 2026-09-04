<?php

namespace App\Filament\Resources\KodeSpms;

use App\Filament\Resources\KodeSpms\Pages\CreateKodeSpm;
use App\Filament\Resources\KodeSpms\Pages\EditKodeSpm;
use App\Filament\Resources\KodeSpms\Pages\ListKodeSpms;
use App\Filament\Resources\KodeSpms\Schemas\KodeSpmForm;
use App\Filament\Resources\KodeSpms\Tables\KodeSpmsTable;
use App\Models\KodeSpm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KodeSpmResource extends Resource
{
    protected static ?string $model = KodeSpm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Rekap SP2D';

    protected static ?string $recordTitleAttribute = 'kode';

    public static function form(Schema $schema): Schema
    {
        return KodeSpmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KodeSpmsTable::configure($table);
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
            'index' => ListKodeSpms::route('/'),
            'create' => CreateKodeSpm::route('/create'),
            'edit' => EditKodeSpm::route('/{record}/edit'),
        ];
    }
}
