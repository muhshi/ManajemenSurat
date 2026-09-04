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
    </style>
</head>
<body>
    <h2 class="text-center">Data Rekap SP2D</h2>
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
            @foreach($records as $record)
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
</body>
</html>
