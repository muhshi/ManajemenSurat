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
        Schema::table('sp2d_rekaps', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['upload_id']);
            
            // Modify column to be nullable
            $table->unsignedBigInteger('upload_id')->nullable()->change();
            
            // Re-add foreign key with 'set null' on delete
            $table->foreign('upload_id')
                ->references('id')->on('sp2d_uploads')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp2d_rekaps', function (Blueprint $table) {
            $table->dropForeign(['upload_id']);
            
            $table->unsignedBigInteger('upload_id')->nullable(false)->change();
            
            $table->foreign('upload_id')
                ->references('id')->on('sp2d_uploads')
                ->cascadeOnDelete();
        });
    }
};
