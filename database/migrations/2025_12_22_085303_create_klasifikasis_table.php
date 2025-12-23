<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('klasifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        $data = [
            'PS.000' => 'Pengkajian dan Pengusulan Kebijakan',
            'PS.100' => 'Penyiapan Kebijakan',
            'PS.200' => 'Masukan dan Dukungan dalam Penyusunan Kebijakan',
            'PS.300' => 'Pengembangan disain dan standardisasi',
            'PS.400' => 'Penetapan Norma, Standar, Prosedur dan Kriteria (NSPK)',
            'SS.000' => 'PERENCANAAN',
            'SS.010' => 'Master Plan dan Network Planing',
            'SS.020' => 'Perumusan dan Penyusunan Bahan',
            'SS.021' => 'Penyiapan bahan penyusunan rancangan sensus',
            'SS.022' => 'Penyusunan metode pencacahan sensus',
            'SS.023' => 'Penentuan volume sensus',
            'SS.024' => 'Penyusunan desain penarikan sampel',
            'SS.025' => 'Penyusunan kerangka sampel',
            'SS.030' => 'Studi pendahuluan (desk study)',
            'SS.100' => 'PERSIAPAN SENSUS',
            'SS.110' => 'Rancangan organisasi',
            'SS.120' => 'Penyusunan kuesioner',
            'SS.130' => 'Penyusunan konsep dan definisi',
            'SS.140' => 'Penyusunan metodologi (organisasi, lapangan, ukuran statistik)',
            'SS.150' => 'Penyusunan buku pedoman (pencacahan, pengawasan, pengolahan)',
            'SS.160' => 'Penyusunan peta wilayah kerja dan muatan peta wilayah',
            'SS.170' => 'Penyusunan pedoman sosialisasi',
            'SS.180' => 'Penyusun program pengolahan (rule validasi pemeriksaan entri tabulasi)',
            'SS.190' => 'Koordinasi intern/ekstern',
            'SS.200' => 'Pelatihan/Ujicoba',
            'SS.210' => 'Pelatihan instruktur',
            'SS.220' => 'Pelatihan petugas',
            'SS.230' => 'Pelatihan petugas pengolahan',
            'SS.240' => 'Perancangan tabel',
            'SS.250' => 'Pelaksanaan ujicoba kuesioner sensus (meliputi realiabilitas kuesioner dan sistem pengolahan)',
            'SS.260' => 'Pelaksanaan ujicoba kuesioner metodologi sensus (meliputi ujicoba pelaksanaan pencacahan, organisasi dan jumlah sampel)',
            'SS.300' => 'Pelaksanaan lapangan',
            'SS.310' => 'Listing',
            'SS.320' => 'Pemilihan sampel',
            'SS.330' => 'Pengumpulan data',
            'SS.340' => 'Pemeriksaan data',
            'SS.350' => 'Pengawasan lapangan',
            'SS.360' => 'Monitoring kualitas',
            'SS.400' => 'Pengolahan',
            'SS.410' => 'Pengelolaan dokumen (penerimaan/pengiriman, pengelompokkan/batching)',
            'SS.420' => 'Pemeriksaan dokumen dan pengkodean (editing/coding)',
            'SS.430' => 'Perekaman data (entri/scanner)',
            'SS.440' => 'Tabulasi data',
            'SS.450' => 'Pemeriksaan tabulasi',
            'SS.460' => 'Laporan konsistensi tabulasi',
            'SS.500' => 'Analisis dan penyajian hasil sensus',
            'SS.510' => 'Pembahasan angka hasil pengolahan',
            'SS.520' => 'Penyusunan angka sementara',
            'SS.530' => 'Penyusunan angka tetap',
            'SS.540' => 'Penyusunan/pembahasan draft publikasi',
            'SS.550' => 'Analisis data sensus',
            'SS.560' => 'Penyusunan diseminasi hasil sensus',
            'SS.600' => 'Diseminasi hasil sensus',
            'SS.610' => 'Penyusunan bahan diseminasi',
            'SS.620' => 'Sosialisasi hasil sensus melalui berbagai media',
            'SS.630' => 'Layanan promosi statistik',
            'VS.000' => 'PERENCANAAN',
            'VS.010' => 'Master plan dan network planing',
            'VS.020' => 'Perumusan dan penyusunan bahan',
            'VS.021' => 'Penyiapan bahan penyusunan rancangan survei',
            'VS.022' => 'Penyusunan metode pencacahan survei',
            'VS.023' => 'Penentuan volume survei',
            'VS.024' => 'Penyusunan desain penarikan sampel',
            'VS.025' => 'Penyusunan kerangka sampel',
            'VS.030' => 'Studi pendahuluan',
            'VS.100' => 'PERSIAPAN SURVEY',
            'VS.110' => 'Rancangan organisasi',
            'VS.120' => 'Penyusunan kuesioner',
            'VS.130' => 'Penyusunan konsep dan definisi',
            'VS.140' => 'Penyusunan metodologi (organisasi, lapangan, ukuran statistik)',
            'VS.150' => 'Penyusunan buku pedoman (pencacahan, pengawasan, pengolahan)',
            'VS.160' => 'Penyusunan peta wilayah kerja dan muatan peta wilayah',
            'VS.170' => 'Penyusunan pedoman sosialisasi',
            'VS.180' => 'Penyusunan program pengolahan (rule validasi pemeriksaan entri tabulasi)',
            'VS.190' => 'Koordinasi intern/ekstern',
            'VS.200' => 'Pelatihan/ujicoba',
            'VS.210' => 'Pelatihan instruktur',
            'VS.220' => 'Pelatihan petugas',
            'VS.230' => 'Pelatihan petugas pengolahan',
            'VS.240' => 'Prancangan tabel',
            'VS.250' => 'Pelaksanaan ujicoba kuesioner survei (meliputi reliabilitas kuesioner dan sistem pengolahan)',
            'VS.260' => 'Pelaksanaan ujicoba kuesioner metodologi survei (meliputi ujicoba pelaksanaan pencacahan, organisasi dan jumlah sampel)',
            'VS.300' => 'Pelaksanaan lapangan',
            'VS.310' => 'Listing',
            'VS.320' => 'Pemilihan sampel',
            'VS.330' => 'Pengumpulan data',
            'VS.340' => 'Pemeriksaan data',
            'VS.350' => 'Pengawasan lapangan',
            'VS.360' => 'Monitoring kualitas',
            'VS.400' => 'Pengolahan',
            'VS.410' => 'Pengelolaan dokumen (penerimaan/pengiriman, pengelompokkan/batching)',
            'VS.420' => 'Pemeriksaan dokumen dan pengkodean (editing/coding)',
            'VS.430' => 'Perekaman data (entri/scaner)',
            'VS.440' => 'Tabulasi data',
            'VS.450' => 'Pemeriksaan tabulasi',
            'VS.460' => 'Laporan konsistensi tabulasi',
            'VS.500' => 'Analisis dan penyajian hasil survei',
            'VS.510' => 'Pembahasan angka hasil pengolahan',
            'VS.520' => 'Penyusunan angka sementara',
            'VS.530' => 'Penyusunan angka proyeksi/ramalan',
            'VS.540' => 'Penyusunan angka tetap',
            'VS.550' => 'Penyusunan/pembahasan draft publikasi',
            'VS.560' => 'Analisis data survei',
            'VS.570' => 'Penyusunan diseminasi hasil survei',
            'VS.600' => 'Diseminasi hasil survei',
            'VS.610' => 'Penyusunan bahan diseminasi (leaflet, booklet, website, penyusunan CD dan sejenisnya)',
            'VS.620' => 'Sosialisasi hasil survei melalui berbagi media',
            'VS.630' => 'Layanan promosi statistik',
            'KS.000' => 'Kompilasi data',
            'KS.100' => 'Analisis data',
            'KS.200' => 'Penyusunan publikasi',
            'ES' => 'EVALUASI DAN PELAPORAN SENSUS, SURVEY DAN KONSOLIDASI DATA',
        ];

        foreach ($data as $kode => $nama) {
            \Illuminate\Support\Facades\DB::table('klasifikasis')->insert([
                'kode' => $kode,
                'nama' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasis');
    }
};
