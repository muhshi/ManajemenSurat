<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bmns', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->integer('nup');
            $table->string('nama_barang');
            $table->string('jenis_bmn')->nullable();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->integer('umur_aset')->nullable();
            $table->boolean('henti_guna')->default(false);
            $table->decimal('nilai_perolehan', 20, 2)->default(0);
            $table->decimal('nilai_buku', 20, 2)->default(0);
            $table->decimal('nilai_penyusutan', 20, 2)->default(0);
            $table->date('tanggal_perolehan')->nullable();
            $table->string('no_polisi')->nullable();
            $table->string('no_dokumen')->nullable();
            $table->string('status_penggunaan')->nullable();
            $table->enum('intra_extra', ['Intra', 'Ekstra'])->default('Intra');
            $table->boolean('usul_hapus')->default(false);
            $table->text('alamat')->nullable();
            $table->string('kode_register')->nullable()->unique();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
            $table->nullableMorphs('penanggung_jawab');
            $table->text('catatan')->nullable();
            $table->json('foto')->nullable();
            $table->timestamps();

            $table->index(['kode_barang', 'nup']);
            $table->index('jenis_bmn');
            $table->index('kondisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bmns');
    }
};
