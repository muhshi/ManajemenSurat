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
        Schema::create('sp2d_pajaks', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('rekap_id')->constrained('sp2d_rekaps')->onDelete('cascade');
            $blueprint->enum('jenis_pajak', ['PPN', 'PPH21', 'PPH22', 'PPH23', 'PPH_FINAL']);
            $blueprint->bigInteger('jumlah_pajak')->default(0);
            $blueprint->timestamps();

            $blueprint->unique(['rekap_id', 'jenis_pajak']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp2d_pajaks');
    }
};
