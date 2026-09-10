<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sp2dUpload;
use App\Models\Sp2dRekap;
use App\Models\Sp2dPajak;

class Sp2dCoretaxIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sp2d_rekap_balance_validation()
    {
        $user = User::factory()->create();
        
        $upload = Sp2dUpload::create([
            'file_monitoring_sp2d' => 'dummy1.xlsx',
            'file_potongan_spm' => 'dummy2.xlsx',
            'periode_bulan' => '08',
            'periode_tahun' => '2026',
            'user_id' => $user->id,
            'status' => 'done',
        ]);

        $rekap = Sp2dRekap::create([
            'upload_id' => $upload->id,
            'no_sp2d' => '12345',
            'tgl_sp2d' => '2026-08-15',
            'jenis_spm' => '211 Gaji Induk',
            'jalur_transaksi' => 'banyak_pihak',
            'jumlah_potongan' => 100000,
            'status_verifikasi' => 'perlu_rincian'
        ]);

        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap->id,
            'npwp_nik' => '1111',
            'nama_pihak' => 'Pegawai A',
            'kode_akun_pajak' => '411121',
            'nominal_pajak' => 40000,
        ]);

        Sp2dPajak::create([
            'sp2d_rekap_id' => $rekap->id,
            'npwp_nik' => '2222',
            'nama_pihak' => 'Pegawai B',
            'kode_akun_pajak' => '411121',
            'nominal_pajak' => 60000,
        ]);

        $this->assertEquals(100000, $rekap->total_pajak);
        $this->assertTrue($rekap->isBalanced());
    }

}
