<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom untuk mendukung sinkronisasi Master Data dari Sipetra.
 *
 * - is_active    : status aktif (pensiun/pindah/kontrak habis → false)
 * - identity_type: membedakan pegawai vs mitra di level database
 * - period       : periode kontrak mitra ("2026", "sensus_ekonomi_2026")
 * - contract_start/end: tanggal kontrak mitra
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status aktif — false = pensiun/pindah/kontrak habis
            // Jangan hapus user saat false, hanya sembunyikan dari dropdown
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar_url');
                $table->index('is_active');
            }

            // Periode aktif mitra — null untuk pegawai
            if (! Schema::hasColumn('users', 'period')) {
                $table->string('period', 50)->nullable()->after('is_active');
                $table->index('period');
            }

            // Tanggal kontrak mitra
            if (! Schema::hasColumn('users', 'contract_start')) {
                $table->date('contract_start')->nullable()->after('period');
            }

            if (! Schema::hasColumn('users', 'contract_end')) {
                $table->date('contract_end')->nullable()->after('contract_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists(['is_active']);
            $table->dropIndexIfExists(['period']);
            $table->dropColumnIfExists(['is_active', 'period', 'contract_start', 'contract_end']);
        });
    }
};
