<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Print Nota Permintaan (A5)</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            /* Dikurangi dikit agar muat di A5 */
            margin: 0;
            padding: 0;
            background-color: #525659;
            color: #000;
        }

        .no-print {
            text-align: center;
            padding: 15px;
            background: #e0e0e0;
            margin-bottom: 20px;
        }

        .a4-page {
            width: 287mm;
            /* 297 - margins */
            height: 200mm;
            /* 210 - margins */
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: row;
            page-break-after: always;
            break-after: page;
        }

        .a4-page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .a5-nota {
            width: 50%;
            height: 100%;
            padding: 10mm 15mm;
            box-sizing: border-box;
            border-right: 1px dashed #ccc;
            /* Garis potong tengah */
            position: relative;
        }

        .a5-nota:last-child {
            border-right: none;
        }

        @media print {
            body {
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .a4-page {
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                width: 100%;
                height: 100vh;
                page-break-after: always;
            }
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }

        .logo {
            width: 45px;
            height: auto;
        }

        .kop-text {
            font-weight: bold;
            font-size: 12px;
            line-height: 1.2;
        }

        .kop-text-address {
            font-size: 8px;
            font-weight: normal;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            text-decoration: underline;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #111;
            padding: 4px;
            font-size: 10px;
        }

        .data-table th {
            text-align: center;
            font-weight: normal;
        }

        .text-center {
            text-align: center;
        }

        .col-no {
            width: 5%;
            text-align: center;
        }

        .col-nama {
            width: 45%;
        }

        .col-banyak {
            width: 15%;
            text-align: center;
        }

        .col-ket {
            width: 35%;
            text-align: center;
        }

        .signatures {
            width: 100%;
            margin-top: 15px;
            font-size: 10px;
        }

        .sig-row {
            width: 100%;
        }

        .sig-left,
        .sig-right {
            display: inline-block;
            width: 49%;
            text-align: center;
            vertical-align: top;
        }

        .sig-center {
            width: 100%;
            text-align: center;
            margin-top: 30px;
        }

        .signature-line {
            display: inline-block;
            width: 110px;
            border-bottom: 1px solid #000;
            margin-top: 40px;
            height: 10px;
        }

        .nip-text {
            text-align: left;
            margin-left: max(0px, calc(50% - 55px));
            margin-top: 2px;
        }

        .header-r {
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()"
            style="padding: 10px 20px; font-size: 14px; cursor: pointer; font-weight: bold; background: #4CAF50; color: white; border: none; border-radius: 4px;">Print
            PDF (A4 Landscape)</button>
        <button onclick="window.close()"
            style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Tutup</button>
        <p style="margin-top: 10px; font-size: 12px; color: #555;">Pastikan memilih ukuran kertas A4 dengan orientasi
            Landscape pada pengaturan printer.</p>
    </div>

    @php
        // Prepare chunks of 2
        $chunks = collect($grouped)->map(function ($transactions) {
            return (object) [
                'tanggal' => $transactions->first()->tanggal ?? null,
                'transactions' => $transactions
            ];
        })->values()->chunk(2);
    @endphp

    @forelse($chunks as $chunk)
        <div class="a4-page">
            @foreach($chunk as $nota)
                <div class="a5-nota">
                    <table class="header-table">
                        <tr>
                            <td style="width: 55px; text-align: center; vertical-align: middle;">
                                <img src="{{ asset('images/logo_bps.png') }}" class="logo" alt="Logo">
                            </td>
                            <td style="width: 50%;">
                                <div class="kop-text">BADAN PUSAT STATISTIK<br>KABUPATEN DEMAK</div>
                                <div class="kop-text-address">Jl. Sultan Hadiwijaya 23 Demak,<br>Telp (0291) 685445</div>
                            </td>
                            <td style="width: 15%;" class="header-r">
                                No<br>
                                Dibukukan
                            </td>
                            <td style="width: 2%;">
                                :<br>:
                            </td>
                            <td style="width: 33%;">
                                {{ implode(', ', $nota->transactions->pluck('no_dok')->unique()->filter()->toArray()) }}<br>
                                {{ \Carbon\Carbon::parse($nota->tanggal)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>

                    <div class="title">NOTA PERMINTAAN ALAT TULIS KANTOR / CETAKAN / PUBLIKASI</div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-no" style="font-weight: bold;">No</th>
                                <th class="col-nama" style="font-weight: bold;">Nama Barang</th>
                                <th class="col-banyak" style="font-weight: bold;">Banyaknya</th>
                                <th class="col-ket" style="font-weight: bold;">KETERANGAN</th>
                            </tr>
                            <tr>
                                <th class="text-center">(1)</th>
                                <th class="text-center">(2)</th>
                                <th class="text-center">(3)</th>
                                <th class="text-center">(4)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nota->transactions as $index => $trx)
                                <tr>
                                    <td class="col-no">{{ $index + 1 }}.</td>
                                    <td class="col-nama" style="padding-left: 8px;">
                                        {{ ucwords(strtolower(optional($trx->item)->item_name ?? $trx->item_code)) }}</td>
                                    <td class="col-banyak">{{ $trx->keluar_unit > 0 ? $trx->keluar_unit : $trx->masuk_unit }}</td>
                                    <td class="col-ket">{{ $trx->keterangan }}</td>
                                </tr>
                            @endforeach

                            @for ($i = count($nota->transactions) + 1; $i <= max(12, count($nota->transactions) + 1); $i++)
                                <tr>
                                    <td class="col-no text-center">{{ $i }}.</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>

                    <div class="signatures">
                        <div class="sig-row">
                            <div class="sig-left">
                                Yang Menerima
                                <br>
                                <br>
                                <div class="signature-line"></div>
                                <div class="nip-text">NIP.</div>
                            </div>

                            <div class="sig-right">
                                Demak,
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($nota->tanggal)->format('d/m/Y') }}
                                <br>
                                <br>
                                <div class="signature-line"></div>
                                <div class="nip-text">NIP.</div>
                            </div>
                        </div>

                        <div class="sig-center">
                            SETUJU DIKELUARKAN<br>
                            <div style="margin-top: 4px;">Kasubbag Umum</div>
                            <div class="signature-line" style="margin-top: 50px;"></div>
                            <div style="font-weight: bold; text-decoration: underline; margin-top: 2px;">SUCIPTO, ST</div>
                            <div>NIP. 19780630 200604 1 003</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div style="padding: 20px; text-align: center; color: red; background: white;">
            Tidak ada data transaksi yang ditemukan.
        </div>
    @endforelse
</body>

</html>