<?php

namespace App\Filament\Resources\Sp2dRekapResource\Widgets;

use App\Models\Sp2dRekap;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class Sp2dRekapStatsOverview extends BaseWidget
{
    /** @var array<string, int> */
    #[Reactive]
    public $paginators = [];

    #[Reactive]
    public ?int $tableRecordsCount = null;

    #[Reactive]
    public ?array $tableColumnSearches = [];

    #[Reactive]
    public ?string $tableGrouping = null;

    #[Reactive]
    public ?array $tableFilters = null;

    #[Reactive]
    public int | string | null $tableRecordsPerPage = null;

    #[Reactive]
    public $tableSearch = '';

    #[Reactive]
    public ?string $tableSort = null;

    #[Reactive]
    public ?string $activeTab = null;

    #[Reactive] #[Locked]
    public ?Model $parentRecord = null;

    protected ?string $pollingInterval = null;

    public function getFilteredQuery(): Builder
    {
        $query = Sp2dRekap::query();
        $filters = $this->tableFilters['filters'] ?? [];

        return $query
            ->when(!empty($filters['bulan']), fn ($q) => $q->whereMonth('tgl_sp2d', $filters['bulan']))
            ->when(!empty($filters['tahun']), fn ($q) => $q->whereYear('tgl_sp2d', $filters['tahun']))
            ->when(!empty($filters['jenis_spm']), fn ($q) => $q->where('jenis_spm', $filters['jenis_spm']))
            ->when(!empty($filters['jalur_transaksi']), fn ($q) => $q->where('jalur_transaksi', $filters['jalur_transaksi']))
            ->when(!empty($filters['status_verifikasi']), fn ($q) => $q->where('status_verifikasi', $filters['status_verifikasi']))
            ->when(!empty($this->tableSearch), function ($q) {
                $search = $this->tableSearch;
                $q->where(function ($sub) use ($search) {
                    $sub->where('no_sp2d', 'like', "%{$search}%")
                        ->orWhere('uraian', 'like', "%{$search}%")
                        ->orWhere('jenis_spm', 'like', "%{$search}%");
                });
            });
    }

    protected function getStats(): array
    {
        $query = $this->getFilteredQuery();
        
        $total = (clone $query)->count();
        $valid = (clone $query)->where('status_verifikasi', 'valid')->count();
        $perluRincian = (clone $query)->where('status_verifikasi', 'perlu_rincian')->count();

        return [
            Stat::make('Total SP2D', $total)
                ->description('Total dokumen SP2D')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
                
            Stat::make('SP2D Valid', $valid)
                ->description('Siap untuk direkap')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Perlu Rincian', $perluRincian)
                ->description('Membutuhkan input rincian')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($perluRincian > 0 ? 'danger' : 'success'),
        ];
    }
}
