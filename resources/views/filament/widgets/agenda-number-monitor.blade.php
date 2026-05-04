<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-m-presentation-chart-line" icon-color="primary">
        <x-slot name="heading">
            Monitor Nomor Agenda
        </x-slot>

        <x-slot name="description">
            Memantau gap urutan penomoran surat di tahun {{ $year }}
        </x-slot>

        <x-slot name="headerEnd">
            <div class="flex items-center gap-x-3">
                <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                    {{ $totalSkipped }} Nomor Terlewat
                </x-filament::badge>

                <div class="w-32">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="year">
                            @foreach ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-slot>

        <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @forelse ($skippedByMonth as $month => $ranges)
                <div
                    class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                    <div class="grid gap-y-2">
                        <div class="flex items-center gap-x-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $month }}
                            </span>
                        </div>

                        <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            {{ $ranges }}
                        </div>

                        <div class="flex items-center gap-x-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                            <x-heroicon-m-exclamation-triangle class="h-4 w-4" />
                            <span>Perlu Penyesuaian</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 flex flex-col items-center justify-center text-center">
                    <div class="rounded-full bg-success-50 p-3 dark:bg-success-950/20">
                        <x-heroicon-o-check-badge class="h-10 w-10 text-success-600" />
                    </div>
                    <h4 class="mt-4 text-lg font-bold text-gray-950 dark:text-white">Semua Nomor Berurutan</h4>
                    <p class="text-sm text-gray-500">Tidak ada nomor agenda yang terlewat di tahun {{ $year }}.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>