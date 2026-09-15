<!DOCTYPE html>
<html>
<head>
    <title>Data Rekap SP2D</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        $groupedRecords = [];
        foreach($records as $record) {
            $month = $record->tgl_sp2d ? \Carbon\Carbon::parse($record->tgl_sp2d)->format('Y-m') : 'Belum Ditentukan';
            $monthName = $record->tgl_sp2d ? 'Periode ' . \Carbon\Carbon::parse($record->tgl_sp2d)->translatedFormat('F Y') : 'Periode Belum Ditentukan';
            $groupedRecords[$month]['name'] = $monthName;
            $groupedRecords[$month]['records'][] = $record;
        }
        ksort($groupedRecords);
        $isFirst = true;
    @endphp

    @foreach($groupedRecords as $month => $group)
        @if(!$isFirst)
            <pagebreak />
        @endif

        @php
            $headingText = 'Data Rekap SP2D — ' . $group['name'];
        @endphp

        <bookmark content="{{ trim($headingText) }}" level="0" />

        <h1 style="font-size: 14px; text-align: center; margin-bottom: 10px;">
            Data Rekap SP2D &mdash; {{ $group['name'] }}
        </h1>

        <table>
            <thead>
                <tr>
                    <th>No SP2D</th>
                    <th>Tgl SP2D</th>
                    <th>Jenis SPM</th>
                    <th>Jalur Transaksi</th>
                    <th class="text-right">Bruto (Pengeluaran)</th>
                    <th class="text-right">Total Potongan</th>
                    <th class="text-right">Netto (Pembayaran)</th>
                    <th>Status</th>
                    <th>Uraian</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group['records'] as $record)
                    <tr>
                        <td>{{ $record->no_sp2d }}</td>
                        <td>{{ $record->tgl_sp2d ? \Carbon\Carbon::parse($record->tgl_sp2d)->format('d-m-Y') : '' }}</td>
                        <td>{{ $record->jenis_spm }}</td>
                        <td>{{ $record->jalur_transaksi }}</td>
                        <td class="text-right">{{ number_format((float)$record->jumlah_pengeluaran, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((float)$record->jumlah_potongan, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((float)$record->jumlah_pembayaran, 0, ',', '.') }}</td>
                        <td>{{ $record->status_verifikasi == 'valid' ? 'Valid' : 'Perlu Rincian' }}</td>
                        <td>{{ $record->uraian }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php $isFirst = false; @endphp
    @endforeach
</body>
</html>
