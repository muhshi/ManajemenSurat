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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('judul');
            $table->string('perihal');
            $table->string('penerima_undangan');
            $table->string('tempat');
            $table->date('tanggal_rapat');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai')->nullable();
            $table->string('pimpinan_rapat');
            $table->string('narasumber')->nullable();
            $table->string('notulis')->nullable();
            $table->text('isi_notulensi')->nullable();
            $table->text('keputusan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('signer_name');
            $table->string('signer_nip');
            $table->string('signer_title');
            $table->string('signer_city');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('agenda_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->onDelete('cascade');
            $table->string('nama');
            $table->string('jabatan');
            $table->string('no_hp')->nullable();
            $table->boolean('hadir')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_pesertas');
        Schema::dropIfExists('agendas');
    }
};
