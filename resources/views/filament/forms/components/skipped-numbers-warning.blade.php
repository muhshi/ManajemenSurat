@php
    $tahun = $tanggal
        ? (int) date('Y', strtotime($tanggal))
        : now()->year;

    $skipped = \App\Models\Agenda::getSkippedNumbers($tahun);
@endphp

@if (!empty($skipped))
    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800/60 dark:bg-amber-900/20">
        <span class="mt-0.5 text-amber-500 dark:text-amber-400" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
        </span>
        <div>
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                Nomor terlewat di tahun {{ $tahun }}:
            </p>
            <p class="mt-0.5 text-sm text-amber-700 dark:text-amber-400">
                {{ \App\Models\Agenda::formatRanges($skipped) }}
            </p>
        </div>
    </div>
@endif

