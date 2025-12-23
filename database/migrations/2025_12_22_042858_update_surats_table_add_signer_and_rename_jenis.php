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
        Schema::table('surats', function (Blueprint $table) {
            $table->renameColumn('jenis', 'jenis_surat');
            $table->string('signer_name')->nullable();
            $table->string('signer_nip')->nullable();
            $table->string('signer_title')->nullable();
            $table->string('signer_city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->renameColumn('jenis_surat', 'jenis');
            $table->dropColumn(['signer_name', 'signer_nip', 'signer_title', 'signer_city']);
        });
    }
};
