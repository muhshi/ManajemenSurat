<x-filament-widgets::widget>
    @if($this->currentUpload)
    <x-filament::section>
        <div wire:poll.2s="updateProgress">
            <h3 class="text-base font-semibold leading-6 mb-3">Sistem Background Import (Status Terakhir)</h3>
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-gray-900 rounded-lg p-3 overflow-hidden shadow-inner border border-gray-800">
                    <div class="flex justify-between items-center mb-2 border-b border-gray-700 pb-2">
                        <span class="text-xs font-mono text-gray-400 truncate max-w-[70%]">📄 {{ basename($this->currentUpload->filename ?? '') }}</span>
                        @php
                            $color = match($this->currentUpload->status) {
                                'done' => 'text-emerald-400',
                                'failed' => 'text-red-400',
                                'processing' => 'text-amber-400',
                                default => 'text-gray-400'
                            };
                        @endphp
                        <span class="text-xs font-bold uppercase {{ $color }}">
                            {{ $this->currentUpload->status }}
                        </span>
                    </div>
                    @php
                        $lines = array_filter(explode("\n", trim($this->currentUpload->error_log ?? '')));
                        $lastLines = array_slice($lines, -8);
                    @endphp
                    <pre class="text-[11px] leading-relaxed text-emerald-400 font-mono whitespace-pre-wrap max-h-40 overflow-y-auto">@if(empty($lastLines))<span class="text-gray-500">...menunggu output server...</span>@else{{ implode("\n", $lastLines) }}@endif</pre>
                </div>
            </div>
        </div>
    </x-filament::section>
    @endif
</x-filament-widgets::widget>
