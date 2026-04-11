<x-filament-widgets::widget>
    <x-filament::section>
        <div wire:poll.2s>
            <h3 class="text-base font-semibold leading-6 mb-3">Live Progress Server (Proses Terakhir)</h3>
            @php $uploads = $this->getActiveUploads(); @endphp
            @if($uploads->isEmpty())
                <p class="text-sm text-gray-500">Belum ada riwayat proses upload.</p>
            @endif
            <div class="grid grid-cols-1 gap-4">
                @foreach($uploads as $upload)
                    <div class="bg-gray-900 rounded-lg p-3 overflow-hidden shadow-inner border border-gray-800">
                        <div class="flex justify-between items-center mb-2 border-b border-gray-700 pb-2">
                            <span class="text-xs font-mono text-gray-400 truncate max-w-[70%]">📄 {{ basename($upload->filename ?? '') }}</span>
                            @php
                                $color = match($upload->status) {
                                    'done' => 'text-emerald-400',
                                    'failed' => 'text-red-400',
                                    'processing' => 'text-amber-400',
                                    default => 'text-gray-400'
                                };
                            @endphp
                            <span class="text-xs font-bold uppercase {{ $color }}">
                                {{ $upload->status }}
                            </span>
                        </div>
                        @php
                            $lines = array_filter(explode("\n", trim($upload->error_log ?? '')));
                            $lastLines = array_slice($lines, -8);
                        @endphp
                        <pre class="text-[11px] leading-relaxed text-emerald-400 font-mono whitespace-pre-wrap max-h-40 overflow-y-auto">@if(empty($lastLines))<span class="text-gray-500">...menunggu output server...</span>@else{{ implode("\n", $lastLines) }}@endif</pre>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
