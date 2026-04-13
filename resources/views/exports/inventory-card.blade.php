<table>
    <tr>
        <td colspan="6"><b>KARTU PERSEDIAAN BARANG PAKAI HABIS ( ATK / ARK )</b></td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>
    <tr>
        <td colspan="2">BADAN PUSAT STATISTIK</td>
        <td>Nama Barang</td>
        <td colspan="2">: {{ $item->item_name }}</td>
        <td>Halaman : 1</td>
    </tr>
    <tr>
        <td colspan="2">KABUPATEN DEMAK</td>
        <td>Kode Barang</td>
        <td colspan="2">: {{ $item->item_code }}</td>
        <td>Proyek / Rutin : -</td>
    </tr>
    <tr>
        <td colspan="2">Jalan Sultan Hadiwijaya No 23 Demak</td>
        <td>Satuan Barang</td>
        <td colspan="2">: {{ $item->satuan }}</td>
        <td>Tahun : {{ $year }}</td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>
    <tr>
        <th align="center"><b>Tanggal</b></th>
        <th align="center"><b>Keterangan</b></th>
        <th align="center"><b>No. Dokumen</b></th>
        <th align="center"><b>Masuk (Unit)</b></th>
        <th align="center"><b>Keluar (Unit)</b></th>
        <th align="center"><b>Saldo (Unit)</b></th>
    </tr>

    @foreach ($transactions as $tx)
    <tr>
        <td>{{ $tx->tanggal ? \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') : '-' }}</td>
        <td>{{ $tx->keterangan }}</td>
        <td>{{ $tx->no_dok ?? '-' }}</td>
        <td align="right">{{ $tx->masuk_unit > 0 ? $tx->masuk_unit : '' }}</td>
        <td align="right">{{ $tx->keluar_unit > 0 ? $tx->keluar_unit : '' }}</td>
        <td align="right">{{ $tx->saldo_unit }}</td>
    </tr>
    @endforeach
</table>
