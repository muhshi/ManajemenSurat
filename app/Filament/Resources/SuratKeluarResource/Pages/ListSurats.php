<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Filament\Resources\SuratKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSurats extends ListRecords
{
    protected static string $resource = SuratKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'surat_keluar' => Tab::make('Surat Keluar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Surat Keluar')),
            'memo' => Tab::make('Memo')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Memo')),
            'surat_pengantar' => Tab::make('Surat Pengantar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_surat', 'Surat Pengantar')),
        ];
    }
}
