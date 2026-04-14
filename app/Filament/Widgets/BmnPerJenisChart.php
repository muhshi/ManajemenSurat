<?php

namespace App\Filament\Widgets;

use App\Models\Bmn;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BmnPerJenisChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Aset per Jenis BMN';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Bmn::query()
            ->select('jenis_bmn', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_bmn')
            ->groupBy('jenis_bmn')
            ->orderByDesc('total')
            ->get();

        $labels = $data->pluck('jenis_bmn')->map(fn ($j) => match ($j) {
            'TANAH'                      => 'Tanah',
            'ALAT BESAR'                 => 'Alat Besar',
            'ALAT ANGKUTAN BERMOTOR'     => 'Alat Angkutan',
            'BANGUNAN DAN GEDUNG'        => 'Bangunan',
            'MESIN PERALATAN NON TIK'    => 'Mesin Non TIK',
            'MESIN PERALATAN KHUSUS TIK' => 'Mesin TIK',
            'ASET TETAP LAINNYA'         => 'Aset Lainnya',
            'ASET TAK BERWUJUD'          => 'Tak Berwujud',
            'RUMAH NEGARA'               => 'Rumah Negara',
            default                      => $j,
        })->toArray();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(107, 114, 128, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(236, 72, 153, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Aset',
                    'data'            => $data->pluck('total')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
