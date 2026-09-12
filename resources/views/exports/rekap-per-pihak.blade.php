<!DOCTYPE html>
<html>
<head>
    <title>Rekap Per Pihak</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .month-title { font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
        .page-break { page-break-before: always; }
        /* DomPDF PDF bookmark support */
        h2.pdf-bookmark { bookmark-level: 1; }
        h3.pdf-bookmark-sub { bookmark-level: 2; font-size: 11px; font-weight: bold; margin: 8px 0 4px 0; }
    </style>
</head>
<body>
    @php $isFirst = true; @endphp
    @foreach($months as $monthData)
        @if(!$isFirst)
            <div class="page-break"></div>
        @endif

        @php
            $tahunLabel = $filterTahun ?? '';
            $bulanLabel = $monthData['bulanName'] ?? '';
            $bookmarkLabel = count($months) > 1
                ? "Rekap Per Pihak — {$bulanLabel} {$tahunLabel}"
                : "Rekap Per Pihak" . ($tahunLabel ? " — {$tahunLabel}" : '');
        @endphp

        {{-- Heading dengan PDF bookmark level 1 --}}
        <h2 class="text-center pdf-bookmark" style="bookmark-label: '{{ $bookmarkLabel }}';">Laporan Rekapitulasi Potongan Pajak Per Pihak</h2>
        @if(!empty($filterBulan) || !empty($filterTahun))
            <p class="text-center">Periode: {{ $filterBulan ?? '' }} {{ $filterTahun ?? '' }}</p>
        @endif

        @if(count($months) > 1)
            {{-- Sub-bookmark level 2 per bulan --}}
            <h3 class="pdf-bookmark-sub" style="bookmark-label: 'Bulan: {{ $bulanLabel }} {{ $tahunLabel }}';">Bulan: {{ $bulanLabel }} {{ $tahunLabel }}</h3>
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

