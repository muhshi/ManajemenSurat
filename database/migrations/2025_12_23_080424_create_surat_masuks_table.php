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
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            // Section 1: Identitas Pengirim
            $table->string('nama_pengirim')->nullable();
            $table->string('jabatan_pengirim')->nullable();
            $table->string('instansi_pengirim')->nullable();

            // Section 2: Detail Surat
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->string('perihal')->nullable();
            $table->text('isi_ringkas')->nullable();
            $table->string('file_surat')->nullable(); // Path to PDF/Image

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuks');
    }
};
