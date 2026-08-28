<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sp2dUpload;
use App\Models\Sp2dRekap;
use App\Models\Sp2dPajak;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DummySp2dSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Create a dummy upload record
        $upload = Sp2dUpload::create([
            'file_monitoring_sp2d' => 'dummy/dummy_sp2d.xlsx',
            'periode_bulan' => '08',
            'periode_tahun' => '2026',
            'status' => 'completed',
            'user_id' => 1, 
        ]);

        // 1. Jalur 1 Pihak (Valid)
        $rekap1 = Sp2dRekap::create([
            'upload_id' => $upload->id,
            'no_spm' => '00100/123456',
            'no_sp2d' => '240800000000100',
            'tgl_spm' => '2026-08-01',
            'tgl_sp2d' => '2026-08-02',
            'jenis_spm' => '301 - UP',
            'jalur_transaksi' => '1_pihak',
            'uraian' => 'Pembayaran Uang Persediaan',
            'atas_nama_default' => 'BPP BPS',
            'jumlah_pengeluaran' => 50000000,
            'jumlah_potongan' => 0,
            'jumlah_pembayaran' => 50000000,
            'status_verifikasi' => 'valid'
        ]);
        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap1->id,
            'npwp_nik' => '123456789012345',
            'nama_pihak' => 'BPP BPS',
            'kode_akun_pajak' => null,
            'nominal_pajak' => 0,
        ]);

        // 2. Banyak Pihak - Status Valid (Pajak Balance)
        $rekap2 = Sp2dRekap::create([
            'upload_id' => $upload->id,
            'no_spm' => '00101/123456',
            'no_sp2d' => '240800000000101',
            'tgl_spm' => '2026-08-05',
            'tgl_sp2d' => '2026-08-06',
            'jenis_spm' => '211 - LS Gaji',
            'jalur_transaksi' => 'banyak_pihak',
            'uraian' => 'Pembayaran Belanja Modal',
            'atas_nama_default' => 'Banyak Pihak',
            'jumlah_pengeluaran' => 11100000, // 10jt dpp + 1.1jt ppn
            'jumlah_potongan' => 1100000,
            'jumlah_pembayaran' => 10000000,
            'status_verifikasi' => 'valid'
        ]);
        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap2->id,
            'npwp_nik' => '987654321012345',
            'nama_pihak' => 'PT Maju Mundur',
            'kode_akun_pajak' => '411211', // PPN
            'dpp' => 10000000,
            'nominal_pajak' => 1100000,
        ]);

        // 3. Banyak Pihak - Perlu Rincian (Pajak Not Balance)
        $rekap3 = Sp2dRekap::create([
            'upload_id' => $upload->id,
            'no_spm' => '00102/123456',
            'no_sp2d' => '240800000000102',
            'tgl_spm' => '2026-08-10',
            'tgl_sp2d' => '2026-08-11',
            'jenis_spm' => '214 - LS Honor',
            'jalur_transaksi' => 'banyak_pihak',
            'uraian' => 'Honorarium Narasumber',
            'atas_nama_default' => 'Banyak Pihak',
            'jumlah_pengeluaran' => 5000000,
            'jumlah_potongan' => 250000, // Misal PPh 21
            'jumlah_pembayaran' => 4750000,
            'status_verifikasi' => 'perlu_rincian'
        ]);
        // Tidak ditambahkan pajak agar status 'perlu_rincian'

        // 4. GUP - Selalu Valid tanpa validasi balance
        $rekap4 = Sp2dRekap::create([
            'upload_id' => $upload->id,
            'no_spm' => '00103/123456',
            'no_sp2d' => '240800000000103',
            'tgl_spm' => '2026-08-15',
            'tgl_sp2d' => '2026-08-16',
            'jenis_spm' => '311 - GUP',
            'jalur_transaksi' => 'gup',
            'uraian' => 'Penggantian Uang Persediaan',
            'atas_nama_default' => 'BPP BPS',
            'jumlah_pengeluaran' => 15000000,
            'jumlah_potongan' => 1500000, // PPN PPh yang disetor BPP
            'jumlah_pembayaran' => 13500000,
            'status_verifikasi' => 'valid'
        ]);
        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap4->id,
            'npwp_nik' => '112233445566778',
            'nama_pihak' => 'CV Sumber Makmur',
            'kode_akun_pajak' => '411211',
            'dpp' => 5000000,
            'nominal_pajak' => 550000,
        ]);
        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap4->id,
            'npwp_nik' => '112233445566779',
            'nama_pihak' => 'Toko Laris',
            'kode_akun_pajak' => '411122',
            'dpp' => 5000000,
            'nominal_pajak' => 75000,
        ]);
    }
}
