<?php

namespace App\Filament\Widgets;

use App\Models\Bmn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BmnStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total       = Bmn::count();
        $baik        = Bmn::where('kondisi', 'Baik')->count();
        $rusakRingan = Bmn::where('kondisi', 'Rusak Ringan')->count();
        $rusakBerat  = Bmn::where('kondisi', 'Rusak Berat')->count();
        $hentiGuna   = Bmn::where('henti_guna', true)->count();
        $usulHapus   = Bmn::where('usul_hapus', true)->count();
        $nilaiBuku   = Bmn::sum('nilai_buku');

        return [
            Stat::make('Total Aset BMN', number_format($total))
                ->description('Seluruh aset terdaftar')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Kondisi Baik', number_format($baik))
                ->description(number_format($baik / max($total, 1) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rusak Ringan', number_format($rusakRingan))
                ->description(number_format($rusakRingan / max($total, 1) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Rusak Berat', number_format($rusakBerat))
                ->description(number_format($rusakBerat / max($total, 1) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Henti Guna', number_format($hentiGuna))
                ->description('Tidak dioperasikan')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('gray'),

            Stat::make('Nilai Buku Total', 'Rp ' . number_format($nilaiBuku, 0, ',', '.'))
                ->description('Total nilai buku semua aset')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
