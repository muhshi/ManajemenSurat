<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Pages\RekapPerPihak;
use App\Models\AkunPajak;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

use function Livewire\trigger;

class RekapPajakOverview extends BaseWidget
{
    protected static bool $isDiscovered = false;

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

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return RekapPerPihak::class;
    }

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

        trigger('mount', $page, [], null, null, []);

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

    protected function getPageTableQuery(): Builder
    {
        return $this->getTablePageInstance()->getFilteredSortedTableQuery();
    }

    protected function getStats(): array
    {
        $subquery = $this->getPageTableQuery()->reorder();
        
        $queryBuilder = DB::table(DB::raw("({$subquery->toSql()}) as sub"))
            ->mergeBindings($subquery->getQuery());
        
        $akuns = AkunPajak::orderBy('kode')->get();
        $selects = ["SUM(total) as grand_total"];
        
        foreach ($akuns as $akun) {
            $selects[] = "SUM(pajak_{$akun->kode}) as pajak_{$akun->kode}";
        }
        
        $result = $queryBuilder->selectRaw(implode(', ', $selects))->first();

        $stats = [];

        foreach ($akuns as $akun) {
            $columnName = 'pajak_' . $akun->kode;
            $val = $result->$columnName ?? 0;
            
            if ($val > 0) {
                $stats[] = Stat::make($akun->kode . ' - ' . $akun->nama_pendek, 'Rp' . number_format((float) $val, 0, ',', '.'));
            }
        }
        
        $grandTotal = $result->grand_total ?? 0;
        $stats[] = Stat::make('Total Keseluruhan', 'Rp' . number_format((float) $grandTotal, 0, ',', '.'))
            ->color('success');

        return $stats;
    }
}
