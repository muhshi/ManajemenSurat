<?php

namespace App\Filament\Resources\Sp2dRekapResource\Widgets;

use App\Models\Sp2dRekap;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\Sp2dRekapResource\Pages\ListSp2dRekaps;
use Filament\Tables\Contracts\HasTable;
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

    protected HasTable $tablePage;

    protected function getTablePageMountParameters(): array
    {
        return [];
    }

    protected function getTablePageInstance(): HasTable
    {
        if (isset($this->tablePage)) {
            return $this->tablePage;
        }

        /** @var HasTable $tableComponent */
        $page = app('livewire')->new($this->getTablePage());

        \Livewire\trigger('mount', $page, [], null, null, []);

        foreach ([
            'activeTab' => $this->activeTab,
            'paginators' => $this->paginators,
            'parentRecord' => $this->parentRecord,
            'tableColumnSearches' => $this->tableColumnSearches ?? [],
            'tableFilters' => $this->tableFilters,
            'tableGrouping' => $this->tableGrouping,
            'tableRecordsPerPage' => $this->tableRecordsPerPage,
            'tableSearch' => $this->tableSearch,
            'tableSort' => $this->tableSort,
            ...$this->getTablePageMountParameters(),
        ] as $property => $value) {
            $page->{$property} = $value;
        }

        $page->bootedInteractsWithTable();

        return $this->tablePage = $page;
    }

    public function getPageTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->getTablePageInstance()->getFilteredSortedTableQuery();
    }

    public function getPageTableRecords(): \Illuminate\Contracts\Pagination\Paginator | \Illuminate\Database\Eloquent\Collection
    {
        return $this->getTablePageInstance()->getTableRecords();
    }

    protected ?string $pollingInterval = '10s';

    protected function getTablePage(): string
    {
        return ListSp2dRekaps::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        
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
