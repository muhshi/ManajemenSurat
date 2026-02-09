<x-filament::section class="mb-6 shadow-sm rounded-xl">
    <x-slot name="heading">
        Aktivitas Terbaru
    </x-slot>

    <div class="flex flex-col space-y-4">
        @foreach ($this->getActivities() as $activity)
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                <div class="flex-shrink-0">
                    <x-filament::icon :icon="$activity['icon']" class="h-5 w-5 text-gray-400" :style="'color: var(--' . $activity['color'] . '-500)'" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $activity['description'] }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</x-filament::section>