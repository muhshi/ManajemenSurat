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
        Schema::table('agendas', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->integer('nomor_urut')->nullable()->after('nomor_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendas', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('nomor_urut');
        });
    }
};
