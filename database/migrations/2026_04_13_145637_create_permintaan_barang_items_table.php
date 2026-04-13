<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_barang_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_barang_id')
                  ->constrained('permintaan_barangs')
                  ->cascadeOnDelete();
            $table->string('nama_item');
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('satuan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_barang_items');
    }
};
