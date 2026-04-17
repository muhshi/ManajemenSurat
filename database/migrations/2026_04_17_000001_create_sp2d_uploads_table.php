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
        Schema::create('sp2d_uploads', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('filename');
            $blueprint->string('periode', 7); // YYYY-MM
            $blueprint->integer('total_rows')->unsigned()->nullable();
            $blueprint->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $blueprint->text('error_log')->nullable();
            $blueprint->foreignId('uploaded_by')->nullable()->constrained('users');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp2d_uploads');
    }
};
