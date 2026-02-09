<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card 1: Surat Masuk -->
        <div class="rounded-xl shadow-lg overflow-hidden relative flex flex-col pt-6 text-white"
            style="background: linear-gradient(135deg, #60a5fa 0%, #1d4ed8 100%);">

            <div class="px-6 pb-6 flex-1 z-10 relative">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col pt-1 pr-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-filament::icon icon="heroicon-o-inbox-arrow-down" class="w-6 h-6 text-white opacity-90"
                                style="color: white;" />
                            <h2 class="text-lg font-bold tracking-wide uppercase opacity-90" style="color: white;">Surat
                                Masuk</h2>
                        </div>
                        <p class="text-sm font-medium text-white/80" style="color: rgba(255,255,255,0.8);">Total Surat
                            Masuk</p>
                    </div>
                    <div class="text-6xl font-black leading-none tracking-tight" style="color: white;">
                        {{ $suratMasukCount }}</div>
                </div>
            </div>

            <a href="{{ $suratMasukUrl }}"
                class="block w-full text-center py-3 hover:bg-white/20 transition duration-150 ease-in-out text-sm font-semibold tracking-wide text-white z-10 relative"
                style="background-color: rgba(0,0,0,0.15); color: white;">
                Selengkapnya &rarr;
            </a>
        </div>

        <!-- Card 2: Surat Keluar -->
        <div class="rounded-xl shadow-lg overflow-hidden relative flex flex-col pt-6 text-white"
            style="background: linear-gradient(135deg, #34d399 0%, #047857 100%);">

            <div class="px-6 pb-6 flex-1 z-10 relative">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col pt-1 pr-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-filament::icon icon="heroicon-o-paper-airplane" class="w-6 h-6 text-white opacity-90"
                                style="color: white;" />
                            <h2 class="text-lg font-bold tracking-wide uppercase opacity-90" style="color: white;">Surat
                                Keluar</h2>
                        </div>
                        <p class="text-sm font-medium text-white/80" style="color: rgba(255,255,255,0.8);">Surat Keluar,
                            Memo, Pengantar</p>
                    </div>
                    <div class="text-6xl font-black leading-none tracking-tight" style="color: white;">
                        {{ $suratKeluarCount }}</div>
                </div>
            </div>

            <a href="{{ $suratKeluarUrl }}"
                class="block w-full text-center py-3 hover:bg-white/20 transition duration-150 ease-in-out text-sm font-semibold tracking-wide text-white z-10 relative"
                style="background-color: rgba(0,0,0,0.15); color: white;">
                Selengkapnya &rarr;
            </a>
        </div>

        <!-- Card 3: Surat Keputusan -->
        <div class="rounded-xl shadow-lg overflow-hidden relative flex flex-col pt-6 text-white"
            style="background: linear-gradient(135deg, #fb923c 0%, #c2410c 100%);">

            <div class="px-6 pb-6 flex-1 z-10 relative">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col pt-1 pr-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-filament::icon icon="heroicon-o-document-text" class="w-6 h-6 text-white opacity-90"
                                style="color: white;" />
                            <h2 class="text-lg font-bold tracking-wide uppercase opacity-90" style="color: white;">Surat
                                Keputusan</h2>
                        </div>
                        <p class="text-sm font-medium text-white/80" style="color: rgba(255,255,255,0.8);">Total SK
                            Diterbitkan</p>
                    </div>
                    <div class="text-6xl font-black leading-none tracking-tight" style="color: white;">{{ $skCount }}
                    </div>
                </div>
            </div>

            <a href="{{ $skUrl }}"
                class="block w-full text-center py-3 hover:bg-white hover:bg-opacity-10 transition duration-150 ease-in-out text-sm font-semibold tracking-wide text-white z-10 relative"
                style="background-color: rgba(0,0,0,0.15); color: white;">
                Selengkapnya &rarr;
            </a>
        </div>
    </div>
</x-filament-widgets::widget>