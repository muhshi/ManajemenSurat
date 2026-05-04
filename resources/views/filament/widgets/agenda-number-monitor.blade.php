<x-filament-widgets::widget>
    <x-filament::section 
        icon="heroicon-o-presentation-chart-line" 
        icon-color="primary"
    >
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-x-3">
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight text-gray-950 dark:text-white">
                        Monitor Nomor Agenda
                    </span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Memantau gap urutan nomor surat di tahun {{ $year }}
                    </span>
                </div>

                <div class="flex items-center gap-x-3">
                    <div class="flex items-center gap-x-1.5 rounded-lg bg-gray-100 px-2.5 py-1 dark:bg-gray-800">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">
                            {{ $totalSkipped }} Nomor Terlewat
                        </span>
                    </div>

                    <select 
                        wire:model.live="year"
                        class="block rounded-lg border-none bg-white py-1.5 text-sm font-semibold text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                    >
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pt-2">
            @forelse ($skippedByMonth as $month => $ranges)
                <div class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition duration-300 hover:border-primary-500 hover:shadow-xl dark:border-white/10 dark:bg-gray-900/50 dark:hover:border-primary-400">
                    <!-- Background Decoration -->
                    <div class="absolute -right-4 -top-4 text-gray-50 opacity-10 transition-colors group-hover:text-primary-500 dark:text-gray-800">
                        <x-heroicon-o-calendar class="h-20 w-20" />
                    </div>

                    <div class="relative flex flex-col gap-y-1">
                        <span class="text-xs font-black uppercase tracking-widest text-primary-600 dark:text-primary-400">
                            {{ $month }}
                        </span>
                        
                        <div class="flex items-baseline gap-x-2">
                            <span class="font-mono text-lg font-bold tracking-tighter text-gray-950 dark:text-white">
                                {{ $ranges }}
                            </span>
                        </div>
                        
                        <div class="mt-2 flex items-center gap-x-1 text-[10px] font-medium text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-exclamation-triangle class="h-3 w-3 text-amber-500" />
                            Silakan cek kembali urutan nomor
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-10">
                    <div class="rounded-full bg-success-50 p-4 dark:bg-success-900/20">
                        <x-heroicon-o-check-badge class="h-10 w-10 text-success-600" />
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-gray-950 dark:text-white">Urutan Nomor Sempurna</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 italic text-center">
                        Tidak ditemukan nomor urut yang terlewat di tahun {{ $year }}.<br>
                        Semua surat terarsip dengan rapi.
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
