<?php

namespace App\Filament\Widgets;

use App\Models\Agenda;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AgendaNumberMonitor extends Widget
{
    protected string $view = 'filament.widgets.agenda-number-monitor';

    protected static ?int $sort = -2;

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    protected function getViewData(): array
    {
        $skippedByMonth = $this->getSkippedByMonth($this->year);

        return [
            'skippedByMonth' => $skippedByMonth,
            'years' => Agenda::select(DB::raw('DISTINCT EXTRACT(YEAR FROM tanggal_rapat) as year'))
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->union([now()->year])
                ->unique()
                ->toArray(),
        ];
    }

    protected function getSkippedByMonth(int $year): array
    {
        $agendas = Agenda::whereYear('tanggal_rapat', $year)
            ->whereNotNull('nomor_urut')
            ->orderBy('nomor_urut')
            ->get(['nomor_urut', 'tanggal_rapat']);

        if ($agendas->isEmpty()) {
            return [];
        }

        $usedNumbers = $agendas->pluck('nomor_urut')->toArray();
        $max = max($usedNumbers);
        $skipped = array_diff(range(1, $max), $usedNumbers);

        if (empty($skipped)) {
            return [];
        }

        // Group skipped numbers by month
        // Logic: if a number X is skipped, we look at the next available number Y > X.
        // The skipped number X likely belongs to the same month as Y (or the month transition).
        $result = [];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($skipped as $num) {
            $nextAgenda = $agendas->first(fn($a) => $a->nomor_urut > $num);
            $monthNum = $nextAgenda ? $nextAgenda->tanggal_rapat->month : $agendas->last()->tanggal_rapat->month;
            $monthName = $months[$monthNum];
            
            $result[$monthName][] = $num;
        }

        // Format ranges for each month
        foreach ($result as $month => $nums) {
            $result[$month] = Agenda::formatRanges($nums);
        }

        return $result;
    }

    public function updatedYear(): void
    {
        // This will trigger a re-render
    }
}
