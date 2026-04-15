<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('transactions')
            ->where('no_dok', 'like', "%\n%")
            ->orWhere('no_dok', 'like', "%\r%")
            ->update([
                'no_dok' => DB::raw("REPLACE(REPLACE(no_dok, '\r', ''), '\n', '')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversed needed
    }
};
