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
        Schema::table('users', function (Blueprint $table) {
            $table->string('sipetra_id')->nullable()->unique()->after('id');
            $table->text('sipetra_token')->nullable()->after('password');
            $table->text('sipetra_refresh_token')->nullable()->after('sipetra_token');
            $table->string('nip_baru')->nullable()->after('nip');
            $table->string('sobat_id')->nullable()->after('nip_baru');
            $table->string('kd_satker')->nullable()->after('sobat_id');
            $table->string('unit_kerja')->nullable()->after('jabatan');
            $table->string('identity_type')->nullable()->after('sipetra_id');
            $table->string('jenis_kelamin')->nullable()->after('nomor_hp');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('pendidikan')->nullable()->after('tanggal_lahir');
            $table->string('avatar_url')->nullable()->after('pendidikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sipetra_id', 'sipetra_token', 'sipetra_refresh_token',
                'nip_baru', 'sobat_id', 'kd_satker', 'unit_kerja',
                'identity_type', 'jenis_kelamin', 'tempat_lahir',
                'tanggal_lahir', 'pendidikan', 'avatar_url'
            ]);
        });
    }
};
