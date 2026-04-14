<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = [
            [
                'nama'    => 'SUCIPTO, ST',
                'nip'     => null,
                'jabatan' => null,
                'no_hp'   => null,
                'aktif'   => true,
            ],
            [
                'nama'    => 'KHOMARUDIN, SST',
                'nip'     => null,
                'jabatan' => null,
                'no_hp'   => null,
                'aktif'   => true,
            ],
        ];

        foreach ($pegawais as $pegawai) {
            Pegawai::updateOrCreate(
                ['nama' => $pegawai['nama']],
                $pegawai
            );
        }
    }
}
