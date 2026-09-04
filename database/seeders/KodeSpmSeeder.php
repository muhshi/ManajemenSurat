<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KodeSpmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kode' => '231', 'nama' => 'NON GAJI', 'jalur' => '1_pihak'],
            ['kode' => '111', 'nama' => 'NON GAJI KONTRAKTUAL', 'jalur' => '1_pihak'],
            
            ['kode' => '311', 'nama' => 'UP', 'jalur' => 'gup'],
            ['kode' => '321', 'nama' => 'TUP', 'jalur' => 'gup'],
            ['kode' => '312', 'nama' => 'GUP', 'jalur' => 'gup'],
            ['kode' => '317', 'nama' => 'GUP-KKP', 'jalur' => 'gup'],
            ['kode' => '322', 'nama' => 'PTUP', 'jalur' => 'gup'],
            
            ['kode' => '237', 'nama' => 'LS-BANYAK PENERIMA', 'jalur' => 'banyak_pihak'],
            ['kode' => '221', 'nama' => 'GAJI LAINNYA', 'jalur' => 'banyak_pihak'],
            ['kode' => '229', 'nama' => 'GAJI LAINNYA PPPK', 'jalur' => 'banyak_pihak'],
            ['kode' => '272', 'nama' => 'Tunjangan Kinerja Susulan', 'jalur' => 'banyak_pihak'],
            ['kode' => '212', 'nama' => 'GAJI PPPK INDUK', 'jalur' => 'banyak_pihak'],
            ['kode' => '211', 'nama' => 'GAJI INDUK', 'jalur' => 'banyak_pihak'],
            ['kode' => '269', 'nama' => 'Tukin Ke-13', 'jalur' => 'banyak_pihak'],
            ['kode' => '261', 'nama' => 'GAJI KE-13 PNS/TNI/POLRI', 'jalur' => 'banyak_pihak'],
            ['kode' => '262', 'nama' => 'Gaji Ke-13 PPPK', 'jalur' => 'banyak_pihak'],
            ['kode' => '222', 'nama' => 'KEKURANGAN GAJI', 'jalur' => 'banyak_pihak'],
            ['kode' => '259', 'nama' => 'THR Tunkin', 'jalur' => 'banyak_pihak'],
            ['kode' => '223', 'nama' => 'GAJI SUSULAN', 'jalur' => 'banyak_pihak'],
            ['kode' => '252', 'nama' => 'THR PPPK', 'jalur' => 'banyak_pihak'],
            ['kode' => '251', 'nama' => 'THR Gaji PNS/TNI/Polri', 'jalur' => 'banyak_pihak'],
        ];

        foreach ($data as $item) {
            \App\Models\KodeSpm::updateOrCreate(
                ['kode' => $item['kode']],
                ['nama' => $item['nama'], 'jalur' => $item['jalur']]
            );
        }
    }
}
