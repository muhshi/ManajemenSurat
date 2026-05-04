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

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($skippedByMonth as $month => $ranges)
                <div class="rounded-lg bg-white p-4 border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                    <div class="flex flex-col">
                        <p class="text-sm font-bold text-red-500 dark:text-red-400">
                            {{ $month }}
                        </p>
                        <p class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                            {{ $ranges }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 flex flex-col items-center justify-center text-center">
                    <div class="rounded-full bg-success-50 p-3 dark:bg-success-950/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40"
                            height="40" style="width:2.5rem;height:2.5rem;" class="text-success-600">
                            <path fill-rule="evenodd"
                                d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-bold text-gray-950 dark:text-white">Semua Nomor Berurutan</h4>
                    <p class="text-sm text-gray-500">Tidak ada nomor agenda yang terlewat di tahun {{ $year }}.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>