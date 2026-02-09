<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        @foreach ($stats as $stat)
            <a href="{{ $stat['url'] }}" class="block group">
                <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow h-full flex flex-col"
                    style="border-left-color: {{ $stat['color_hex'] }};">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col pt-1 pr-4">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::icon :icon="$stat['icon']" class="w-6 h-6" :style="'color: ' . $stat['color_hex']" />
                                <h3 class="font-bold text-gray-800 text-lg uppercase tracking-wide leading-none">
                                    {{ $stat['label'] }}
                                </h3>
                            </div>
                            <p class="text-sm font-medium text-gray-500">
                                {{ $stat['description'] }}
                            </p>
                        </div>
                        <div class="text-5xl font-extrabold text-gray-800 leading-none tracking-tight">
                            {{ $stat['value'] }}
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>