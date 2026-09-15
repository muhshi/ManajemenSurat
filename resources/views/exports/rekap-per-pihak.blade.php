<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .month-title { font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
    </style>
</head>
<body>
    @php $isFirst = true; @endphp
    @foreach($months as $monthData)
        @if(!$isFirst)
            <pagebreak />
        @endif

        @php
            $tahunLabel = $filterTahun ?? '';
            $bulanLabel = $monthData['bulanName'] ?? '';
            $bookmarkLabel = count($months) > 1
                ? "Rekap Per Pihak — {$bulanLabel} {$tahunLabel}"
                : "Rekap Per Pihak" . ($tahunLabel ? " — {$tahunLabel}" : '');
        @endphp

        <bookmark content="{{ trim($bookmarkLabel) }}" level="0" />

        <h2 class="text-center" style="font-size: 13px; font-weight: bold; margin: 8px 0 4px 0;">Laporan Rekapitulasi Potongan Pajak Per Pihak</h2>
        @if(!empty($filterBulan) || !empty($filterTahun))
            <p class="text-center">Periode: {{ $filterBulan ?? '' }} {{ $filterTahun ?? '' }}</p>
        @endif

        @if(count($months) > 1)
            <h3 style="font-size: 11px; font-weight: bold; margin: 8px 0 4px 0;">Bulan: {{ $bulanLabel }} {{ $tahunLabel }}</h3>
        @endif
        <table>
            <thead>
                <tr>
                    @foreach($monthData['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($monthData['rows'] as $row)
                    <tr>
                        @foreach($row as $index => $cell)
                            <td class="{{ $index > 1 ? 'text-right' : '' }}">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    @foreach($monthData['grandTotal'] as $index => $cell)
                        <th class="{{ $index > 1 ? 'text-right font-bold' : 'font-bold' }}">{{ $cell }}</th>
                    @endforeach
                </tr>
            </tfoot>
        </table>

        @php $isFirst = false; @endphp
    @endforeach
</body>
</html>

