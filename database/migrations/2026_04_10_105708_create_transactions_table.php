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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('inventory_upload_id')->nullable()->constrained('inventory_uploads')->nullOnDelete();
            $table->date('tanggal')->nullable();
            $table->string('keterangan', 100);
            $table->string('no_dok', 80)->nullable();
            $table->integer('masuk_unit')->unsigned()->default(0);
            $table->decimal('masuk_harga', 15, 2)->default(0);
            $table->decimal('masuk_jumlah', 15, 2)->default(0);
            $table->integer('keluar_unit')->unsigned()->default(0);
            $table->decimal('keluar_harga', 15, 2)->default(0);
            $table->decimal('keluar_jumlah', 15, 2)->default(0);
            $table->integer('saldo_unit')->default(0);
            $table->decimal('saldo_harga', 15, 2)->default(0);
            $table->decimal('saldo_jumlah', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
