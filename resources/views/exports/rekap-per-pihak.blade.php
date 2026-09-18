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

        @if(!empty($monthData['details']) && count($monthData['details']) > 0)
            <pagebreak />
            <bookmark content="Lampiran Rincian SP2D — {{ $bulanLabel }} {{ $tahunLabel }}" level="1" />
            
            <h2 class="text-center" style="font-size: 13px; font-weight: bold; margin: 8px 0 4px 0;">Lampiran: Rincian Transaksi Potongan SP2D Per Pihak</h2>
            <p class="text-center" style="margin: 0 0 10px 0; font-size: 9px; color: #555;">Periode: {{ $bulanLabel }} {{ $tahunLabel }}</p>

            <table style="font-size: 8.5px;">
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">No</th>
                        <th style="width: 140px;">Nama Pihak</th>
                        <th style="width: 85px;">No. SP2D</th>
                        <th style="width: 60px;" class="text-center">Tanggal</th>
                        <th style="width: 100px;">Jenis Potongan</th>
                        <th style="width: 80px;" class="text-right">Nominal</th>
                        <th>Uraian SP2D</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noDtl = 1; @endphp
                    @foreach($monthData['details'] as $dtl)
                        <tr>
                            <td class="text-center">{{ $noDtl++ }}</td>
                            <td>
                                <strong>{{ $dtl['nama_pihak'] }}</strong>
                                @if(!empty($dtl['npwp_nik']))
                                    <div style="font-size: 7.5px; color: #555;">NPWP/NIK: {{ $dtl['npwp_nik'] }}</div>
                                @endif
                            </td>
                            <td>{{ $dtl['no_sp2d'] }}</td>
                            <td class="text-center">{{ $dtl['tgl_sp2d'] }}</td>
                            <td>{{ $dtl['nama_pajak'] }}</td>
                            <td class="text-right">Rp{{ number_format((float)$dtl['nominal_pajak'], 0, ',', '.') }}</td>
                            <td style="font-size: 8px; color: #333;">{{ $dtl['uraian'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right font-bold">Total Rincian Potongan</th>
                        <th class="text-right font-bold">Rp{{ number_format((float)$monthData['detailsTotal'], 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        @endif

        @php $isFirst = false; @endphp
    @endforeach
</body>
</html>

