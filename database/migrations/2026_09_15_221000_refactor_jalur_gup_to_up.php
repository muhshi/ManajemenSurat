<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE sp2d_rekaps MODIFY COLUMN jalur_transaksi ENUM('1_pihak', 'banyak_pihak', 'gup', 'up') NULL");
            DB::statement("UPDATE sp2d_rekaps SET jalur_transaksi = 'up' WHERE jalur_transaksi = 'gup'");
            DB::statement("ALTER TABLE sp2d_rekaps MODIFY COLUMN jalur_transaksi ENUM('1_pihak', 'banyak_pihak', 'up') NULL");

            DB::statement("ALTER TABLE kode_spms MODIFY COLUMN jalur ENUM('1_pihak', 'banyak_pihak', 'gup', 'up') NOT NULL DEFAULT '1_pihak'");
            DB::statement("UPDATE kode_spms SET jalur = 'up' WHERE jalur = 'gup'");
            DB::statement("ALTER TABLE kode_spms MODIFY COLUMN jalur ENUM('1_pihak', 'banyak_pihak', 'up') NOT NULL DEFAULT '1_pihak'");
        } else {
            // Untuk SQLite, ubah kolom menjadi string terlebih dahulu untuk menghapus CHECK constraint enum lama
            Schema::table('sp2d_rekaps', function (Blueprint $table) {
                $table->string('jalur_transaksi')->nullable()->change();
            });
            Schema::table('kode_spms', function (Blueprint $table) {
                $table->string('jalur')->default('1_pihak')->change();
            });

            DB::table('sp2d_rekaps')->where('jalur_transaksi', 'gup')->update(['jalur_transaksi' => 'up']);
            DB::table('kode_spms')->where('jalur', 'gup')->update(['jalur' => 'up']);

            Schema::table('sp2d_rekaps', function (Blueprint $table) {
                $table->enum('jalur_transaksi', ['1_pihak', 'banyak_pihak', 'up'])->nullable()->change();
            });
            Schema::table('kode_spms', function (Blueprint $table) {
                $table->enum('jalur', ['1_pihak', 'banyak_pihak', 'up'])->default('1_pihak')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE sp2d_rekaps MODIFY COLUMN jalur_transaksi ENUM('1_pihak', 'banyak_pihak', 'gup', 'up') NULL");
            DB::statement("UPDATE sp2d_rekaps SET jalur_transaksi = 'gup' WHERE jalur_transaksi = 'up'");
            DB::statement("ALTER TABLE sp2d_rekaps MODIFY COLUMN jalur_transaksi ENUM('1_pihak', 'banyak_pihak', 'gup') NULL");

            DB::statement("ALTER TABLE kode_spms MODIFY COLUMN jalur ENUM('1_pihak', 'banyak_pihak', 'gup', 'up') NOT NULL DEFAULT '1_pihak'");
            DB::statement("UPDATE kode_spms SET jalur = 'gup' WHERE jalur = 'up'");
            DB::statement("ALTER TABLE kode_spms MODIFY COLUMN jalur ENUM('1_pihak', 'banyak_pihak', 'gup') NOT NULL DEFAULT '1_pihak'");
        } else {
            Schema::table('sp2d_rekaps', function (Blueprint $table) {
                $table->string('jalur_transaksi')->nullable()->change();
            });
            Schema::table('kode_spms', function (Blueprint $table) {
                $table->string('jalur')->default('1_pihak')->change();
            });

            DB::table('sp2d_rekaps')->where('jalur_transaksi', 'up')->update(['jalur_transaksi' => 'gup']);
            DB::table('kode_spms')->where('jalur', 'up')->update(['jalur' => 'gup']);

            Schema::table('sp2d_rekaps', function (Blueprint $table) {
                $table->enum('jalur_transaksi', ['1_pihak', 'banyak_pihak', 'gup'])->nullable()->change();
            });
            Schema::table('kode_spms', function (Blueprint $table) {
                $table->enum('jalur', ['1_pihak', 'banyak_pihak', 'gup'])->default('1_pihak')->change();
            });
        }
    }
};
