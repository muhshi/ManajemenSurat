<?php

namespace App\Filament\Widgets;

use App\Models\Surat;
use App\Models\SuratMasuk;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class TrenSuratChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Surat';
    protected static ?string $description = 'Perkembangan jumlah surat per periode';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    public ?string $filter = 'surat_masuk';

    protected function getFilters(): ?array
    {
        return [
            'surat_masuk' => 'Surat Masuk',
            'surat_keluar' => 'Surat Keluar',
            'surat_keputusan' => 'Surat Keputusan',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        // Dummy data structure
        $data = [];
        $color = '';

        switch ($activeFilter) {
            case 'surat_keluar':
                $data = [15, 25, 20, 30, 25, 35, 30, 40, 35, 45, 40, 50];
                $color = '#22c55e'; // Green
                break;
            case 'surat_keputusan':
                $data = [5, 8, 12, 10, 15, 12, 18, 15, 20, 25, 22, 28];
                $color = '#f97316'; // Orange
                break;
            case 'surat_masuk':
            default:
                $data = [10, 20, 15, 25, 30, 20, 40, 35, 50, 60, 45, 55];
                $color = '#3b82f6'; // Blue
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => $this->getFilters()[$activeFilter] ?? 'Data',
                    'data' => $data,
                    'borderColor' => $color,
                    'backgroundColor' => 'rgba(255, 255, 255, 0)', // Transparent fill
                    'fill' => false,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointBackgroundColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointHoverRadius' => 5,
                    'pointHoverBackgroundColor' => $color,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                        'drawBorder' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
