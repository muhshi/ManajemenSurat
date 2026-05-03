<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-x-3 pb-4">
            <div class="flex-1">
                <h3 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    Monitor Nomor Surat
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Daftar nomor urut yang belum digunakan atau terlewat.
                </p>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                    Belum Terpakai / Terlewat
                </p>
            </div>

            <div class="flex items-center gap-x-3">
                <select 
                    wire:model.live="year"
                    class="block w-full rounded-lg border-none bg-white py-1.5 pe-8 ps-3 text-sm leading-6 text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @forelse ($skippedByMonth as $month => $ranges)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <h4 class="text-sm font-bold text-red-600 dark:text-red-400">
                        {{ $month }}
                    </h4>
                    <p class="mt-1 text-sm font-extrabold text-gray-950 dark:text-white">
                        {{ $ranges }}
                    </p>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-6 text-gray-500">
                    <x-heroicon-o-check-circle class="h-8 w-8 text-success-500 mb-2" />
                    <p class="text-sm italic">Tidak ada nomor yang terlewat di tahun {{ $year }}.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
