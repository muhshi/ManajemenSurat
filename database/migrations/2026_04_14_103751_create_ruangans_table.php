<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruang')->unique();
            $table->string('nama_tipe_ruang')->nullable();
            $table->string('nama_ruang');
            $table->tinyInteger('lantai')->default(1);
            $table->decimal('luas_ruang', 8, 2)->nullable();
            $table->string('gedung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruangans');
    }
};
