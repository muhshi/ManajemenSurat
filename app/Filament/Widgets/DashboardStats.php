<?php

namespace App\Filament\Widgets;

use App\Models\Surat;
use App\Models\SuratMasuk;
use App\Filament\Resources\SuratMasukResource;
use App\Filament\Resources\SuratKeluarResource;
use App\Filament\Resources\SKResource;
use Filament\Widgets\Widget;

class DashboardStats extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'suratMasukCount' => SuratMasuk::count(),
            'suratKeluarCount' => Surat::where('jenis_surat', '!=', 'SK')->count(),
            'skCount' => Surat::where('jenis_surat', 'SK')->count(),
            'suratMasukUrl' => SuratMasukResource::getUrl('index'),
            'suratKeluarUrl' => SuratKeluarResource::getUrl('index'),
            'skUrl' => SKResource::getUrl('index'),
        ];
    }
}
