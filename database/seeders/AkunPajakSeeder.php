<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AkunPajakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akuns = [
            ['kode' => '411121', 'nama_pendek' => 'PPh 21', 'nama_lengkap' => 'Pendapatan PPh Pasal 21'],
            ['kode' => '411122', 'nama_pendek' => 'PPh 22', 'nama_lengkap' => 'PPh 22'],
            ['kode' => '411124', 'nama_pendek' => 'PPh 23', 'nama_lengkap' => 'PPh 23'],
            ['kode' => '411211', 'nama_pendek' => 'PPN', 'nama_lengkap' => 'PPN'],
            ['kode' => '411128', 'nama_pendek' => 'PPh Final', 'nama_lengkap' => 'PPh Final'],
            ['kode' => '811311', 'nama_pendek' => 'PFK Bulog', 'nama_lengkap' => 'Penerimaan Setoran / Potongan PFK Bulog PNS Pusat'],
            ['kode' => '811211', 'nama_pendek' => 'PFK 2% Gaji Terusan', 'nama_lengkap' => 'Penerimaan Setoran / Potongan PFK 2% Pembayaran Gaji Terusan PNS Pusat'],
            ['kode' => '811111', 'nama_pendek' => 'PFK 10% Gaji', 'nama_lengkap' => 'Penerimaan Setoran / Potongan PFK 10% Gaji PNS Pusat'],
            ['kode' => '811135', 'nama_pendek' => 'PFK Iuran JKN', 'nama_lengkap' => 'Penerimaan Dana PFK Iuran Jaminan Kesehatan PNS Pusat'],
            ['kode' => '425151', 'nama_pendek' => 'Sewa Sarana/Prasarana', 'nama_lengkap' => 'Pendapatan Penggunaan Sarana dan Prasarana sesuai dengan Tusi'],
        ];

        foreach ($akuns as $akun) {
            \App\Models\AkunPajak::updateOrCreate(
                ['kode' => $akun['kode']],
                $akun
            );
        }
    }
}
