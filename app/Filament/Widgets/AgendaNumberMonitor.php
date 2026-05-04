<?php

namespace App\Filament\Widgets;

use App\Models\Agenda;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AgendaNumberMonitor extends Widget
{
    protected string $view = 'filament.widgets.agenda-number-monitor';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function getYears(): array
    {
        return Agenda::selectRaw('YEAR(tanggal_rapat) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray() ?: [now()->year];
    }

    protected function getData(): array
    {
        $tahun = $this->year ?? now()->year;
        
        // Ambil nomor urut yang terpakai beserta bulannya
        $used = Agenda::whereYear('tanggal_rapat', $tahun)
            ->whereNotNull('nomor_urut')
            ->orderBy('nomor_urut')
            ->get(['nomor_urut', 'tanggal_rapat'])
            ->mapWithKeys(fn($item) => [$item->nomor_urut => $item->tanggal_rapat->format('F')])
            ->toArray();

        if (empty($used)) {
            return [
                'skippedByMonth' => [],
                'totalSkipped' => 0,
            ];
        }

        $max = max(array_keys($used));
        $skippedByMonth = [];
        $totalSkipped = 0;

        // Cari gap dan kelompokkan berdasarkan bulan (estimasi)
        for ($i = 1; $i < $max; $i++) {
            if (!isset($used[$i])) {
                // Cari bulan terdekat sebelumnya yang terpakai
                $month = 'Tidak Teridentifikasi';
                for ($j = $i - 1; $j >= 1; $j--) {
                    if (isset($used[$j])) {
                        $month = $used[$j];
                        break;
                    }
                }
                
                if ($month === 'Tidak Teridentifikasi') {
                     // Jika tidak ada sebelumnya, ambil sesudahnya
                     for ($k = $i + 1; $k <= $max; $k++) {
                         if (isset($used[$k])) {
                             $month = $used[$k];
                             break;
                         }
                     }
                }

                $skippedByMonth[$month][] = $i;
                $totalSkipped++;
            }
        }

        // Format ranges untuk setiap bulan
        foreach ($skippedByMonth as $month => $numbers) {
            $skippedByMonth[$month] = Agenda::formatRanges($numbers);
        }

        return [
            'skippedByMonth' => $skippedByMonth,
            'totalSkipped' => $totalSkipped,
        ];
    }

    protected function getViewData(): array
    {
        return array_merge([
            'years' => $this->getYears(),
            'year' => $this->year,
        ], $this->getData());
    }
}
