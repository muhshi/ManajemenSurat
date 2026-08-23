<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update sp2d_uploads
        Schema::table('sp2d_uploads', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['filename', 'periode', 'total_rows', 'uploaded_by']);

            $table->string('file_monitoring_sp2d')->nullable()->after('id');
            $table->string('file_potongan_spm')->nullable()->after('file_monitoring_sp2d');
            $table->string('periode_bulan', 2)->after('file_potongan_spm');
            $table->string('periode_tahun', 4)->after('periode_bulan');
            $table->integer('total_sp2d_terproses')->unsigned()->default(0)->after('periode_tahun');
            $table->foreignId('user_id')->nullable()->after('error_log')->constrained('users')->onDelete('set null');
        });

        // 2. Update sp2d_rekaps
        Schema::table('sp2d_rekaps', function (Blueprint $table) {
            $table->dropUnique(['no_spp', 'periode']);
            
            $table->dropColumn([
                'no_spp', 
                'uraian_spp', 
                'jenis_spp', 
                'tanggal_spp', 
                'tanggal_sp2d', 
                'status_sp2d', 
                'kppn', 
                'nama_satker', 
                'periode'
            ]);

            $table->string('no_spm', 50)->nullable()->after('upload_id');
            $table->date('tgl_spm')->nullable()->after('no_spm');
            $table->string('jenis_spm', 100)->nullable()->after('tgl_spm');
            $table->enum('jalur_transaksi', ['1_pihak', 'banyak_pihak', 'gup'])->nullable()->after('jenis_spm');
            $table->date('tgl_sp2d')->nullable()->after('no_sp2d');
            $table->text('uraian')->nullable()->after('tgl_sp2d');
            $table->string('atas_nama_default')->nullable()->after('jumlah_pembayaran');
            $table->enum('status_verifikasi', ['valid', 'perlu_rincian', 'draft'])->default('draft')->after('atas_nama_default');

            // Kita buat no_sp2d menjadi required saat runtime, tapi table level unique index dengan upload_id
            // Memerlukan no_sp2d unique dalam 1 upload_id agar tidak double
            $table->unique(['upload_id', 'no_sp2d'], 'rekaps_upload_sp2d_unique');
            $table->index('no_sp2d');
            $table->index('tgl_sp2d');
        });

        // 3. Update sp2d_pajaks (Drop and Recreate)
        Schema::dropIfExists('sp2d_pajaks');

        Schema::create('sp2d_pajaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp2d_rekap_id')->constrained('sp2d_rekaps')->onDelete('cascade');
            $table->string('npwp_nik', 30)->nullable();
            $table->string('nama_pihak')->nullable();
            $table->string('kode_akun_pajak', 20)->nullable();
            $table->string('nama_akun_pajak')->nullable();
            $table->bigInteger('dpp')->default(0);
            $table->bigInteger('nominal_pajak')->default(0);
            $table->string('no_drpp_kuitansi')->nullable();
            $table->string('ntpn_billing')->nullable();
            $table->timestamps();

            $table->index('npwp_nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop recreated table
        Schema::dropIfExists('sp2d_pajaks');

        // Restore sp2d_pajaks
        Schema::create('sp2d_pajaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_id')->constrained('sp2d_rekaps')->onDelete('cascade');
            $table->enum('jenis_pajak', ['PPN', 'PPH21', 'PPH22', 'PPH23', 'PPH_FINAL']);
            $table->bigInteger('jumlah_pajak')->default(0);
            $table->timestamps();

            $table->unique(['rekap_id', 'jenis_pajak']);
        });

        // Reverse sp2d_rekaps
        Schema::table('sp2d_rekaps', function (Blueprint $table) {
            $table->dropUnique('rekaps_upload_sp2d_unique');
            $table->dropIndex(['no_sp2d']);
            $table->dropIndex(['tgl_sp2d']);

            $table->dropColumn([
                'no_spm',
                'tgl_spm',
                'jenis_spm',
                'jalur_transaksi',
                'tgl_sp2d',
                'uraian',
                'atas_nama_default',
                'status_verifikasi'
            ]);

            $table->string('no_spp', 30)->nullable();
            $table->text('uraian_spp')->nullable();
            $table->string('jenis_spp', 100)->nullable();
            $table->date('tanggal_spp')->nullable();
            $table->date('tanggal_sp2d')->nullable();
            $table->string('status_sp2d', 100)->nullable();
            $table->string('kppn', 150)->nullable();
            $table->string('nama_satker', 200)->nullable();
            $table->string('periode', 7)->nullable();

            $table->unique(['no_spp', 'periode']);
        });

        // Reverse sp2d_uploads
        Schema::table('sp2d_uploads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            
            $table->dropColumn([
                'file_monitoring_sp2d',
                'file_potongan_spm',
                'periode_bulan',
                'periode_tahun',
                'total_sp2d_terproses',
                'user_id'
            ]);

            $table->string('filename')->nullable();
            $table->string('periode', 7)->nullable();
            $table->integer('total_rows')->unsigned()->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
        });
    }
};
