<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        $ruangans = [
            // Lantai 1 — Gedung Kantor Permanen (NUP 2, Kode 4010101001)
            [
                'kode_ruang'     => '1001',
                'nama_tipe_ruang' => 'Ruang Pelayanan',
                'nama_ruang'     => 'RUANG PELAYANAN PUBLIK',
                'lantai'         => 1,
                'luas_ruang'     => 26.25,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1002',
                'nama_tipe_ruang' => 'Ruang Kerja',
                'nama_ruang'     => 'RUANG SUBBAG UMUM',
                'lantai'         => 1,
                'luas_ruang'     => 25.55,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1003',
                'nama_tipe_ruang' => 'Ruang Kerja',
                'nama_ruang'     => 'RUANG KEPALA',
                'lantai'         => 1,
                'luas_ruang'     => 31.50,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1004',
                'nama_tipe_ruang' => 'Ruang Toilet/WC',
                'nama_ruang'     => 'TOILET RUANG KEPALA',
                'lantai'         => 1,
                'luas_ruang'     => 4.16,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1005',
                'nama_tipe_ruang' => 'Ruang Kerja',
                'nama_ruang'     => 'RUANG IPDS',
                'lantai'         => 1,
                'luas_ruang'     => 24.00,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1006',
                'nama_tipe_ruang' => 'Ruang Istirahat',
                'nama_ruang'     => 'DAPUR',
                'lantai'         => 1,
                'luas_ruang'     => 21.60,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1007',
                'nama_tipe_ruang' => 'Ruang Toilet/WC',
                'nama_ruang'     => 'TOILET LT 1',
                'lantai'         => 1,
                'luas_ruang'     => 3.94,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '1008',
                'nama_tipe_ruang' => 'Ruang Gudang',
                'nama_ruang'     => 'GUDANG',
                'lantai'         => 1,
                'luas_ruang'     => 6.00,
                'gedung'         => null,
            ],
            // Lantai 2
            [
                'kode_ruang'     => '2001',
                'nama_tipe_ruang' => 'Ruang Istirahat',
                'nama_ruang'     => 'MUSHOLLA',
                'lantai'         => 2,
                'luas_ruang'     => 8.10,
                'gedung'         => null,
            ],
            [
                'kode_ruang'     => '2002',
                'nama_tipe_ruang' => 'Ruang Kerja',
                'nama_ruang'     => 'RUANG TEKNIS',
                'lantai'         => 2,
                'luas_ruang'     => 93.79,
                'gedung'         => null,
            ],
            // Gedung Pertemuan (NUP 1, Kode 4010109001)
            [
                'kode_ruang'     => '1009',
                'nama_tipe_ruang' => 'Ruang Rapat Besar',
                'nama_ruang'     => 'RUANG PERTEMUAN',
                'lantai'         => 1,
                'luas_ruang'     => 60.00,
                'gedung'         => 'Gedung Pertemuan',
            ],
        ];

        foreach ($ruangans as $ruangan) {
            Ruangan::updateOrCreate(
                ['kode_ruang' => $ruangan['kode_ruang']],
                $ruangan
            );
        }
    }
}
