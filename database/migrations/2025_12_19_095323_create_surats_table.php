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
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_urut');
            $table->string('nomor_surat')->unique();
            $table->string('judul_surat');
            $table->enum('jenis', ['Pelatihan', 'Pelaksanaan']); // Match form options
            $table->date('tanggal_surat');
            $table->integer('tahun');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
