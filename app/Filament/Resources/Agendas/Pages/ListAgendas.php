<?php

namespace App\Filament\Resources\Agendas\Pages;

use App\Filament\Resources\Agendas\AgendaResource;
use App\Models\Agenda;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAgendas extends ListRecords
{
    protected static string $resource = AgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.list-agendas-header');
    }
}
