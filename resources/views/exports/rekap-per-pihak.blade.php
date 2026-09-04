<!DOCTYPE html>
<html>
<head>
    <title>Rekap Per Pihak</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="text-center">Laporan Rekapitulasi Potongan Pajak Per Pihak</h2>
    @if(!empty($filterBulan) || !empty($filterTahun))
        <p class="text-center">Periode: {{ $filterBulan ?? '' }} {{ $filterTahun ?? '' }}</p>
    @endif
    
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        <td class="{{ $index > 1 ? 'text-right' : '' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                @foreach($grandTotal as $index => $cell)
                    <th class="{{ $index > 1 ? 'text-right font-bold' : 'font-bold' }}">{{ $cell }}</th>
                @endforeach
            </tr>
        </tfoot>
    </table>
</body>
</html>
