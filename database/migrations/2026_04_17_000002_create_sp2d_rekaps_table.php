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
        Schema::create('sp2d_rekaps', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('upload_id')->constrained('sp2d_uploads')->onDelete('cascade');
            $blueprint->string('no_spp', 30);
            $blueprint->text('uraian_spp');
            $blueprint->string('jenis_spp', 100)->nullable();
            $blueprint->date('tanggal_spp')->nullable();
            $blueprint->bigInteger('jumlah_pengeluaran')->default(0);
            $blueprint->bigInteger('jumlah_potongan')->default(0);
            $blueprint->bigInteger('jumlah_pembayaran')->default(0);
            $blueprint->date('tanggal_sp2d')->nullable();
            $blueprint->string('no_sp2d', 50)->nullable();
            $blueprint->string('status_sp2d', 100)->nullable();
            $blueprint->string('kppn', 150)->nullable();
            $blueprint->string('nama_satker', 200)->nullable();
            $blueprint->string('periode', 7); // YYYY-MM
            $blueprint->timestamps();

            $blueprint->unique(['no_spp', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp2d_rekaps');
    }
};
