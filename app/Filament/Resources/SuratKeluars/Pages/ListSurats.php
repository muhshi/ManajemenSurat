<?php

namespace App\Filament\Resources\SuratKeluars\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\SuratKeluars\SuratKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Surat;
use Illuminate\Database\Eloquent\Builder;

class ListSurats extends ListRecords
{
    protected static string $resource = SuratKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(Surat::where('jenis_surat', '!=', 'SK')->count()),
            'surat_keluar' => Tab::make('Surat Keluar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Surat Keluar'))
                ->badge(Surat::where('jenis_surat', 'Surat Keluar')->count()),
            'memo' => Tab::make('Memo')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Memo'))
                ->badge(Surat::where('jenis_surat', 'Memo')->count()),
            'surat_pengantar' => Tab::make('Surat Pengantar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Surat Pengantar'))
                ->badge(Surat::where('jenis_surat', 'Surat Pengantar')->count()),
        ];
    }
}
