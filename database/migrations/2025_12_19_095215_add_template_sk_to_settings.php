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
        // Insert template_sk setting
        \DB::table('settings')->insert([
            'group' => 'office',
            'name' => 'template_sk',
            'locked' => false,
            'payload' => json_encode(null),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('settings')
            ->where('group', 'office')
            ->where('name', 'template_sk')
            ->delete();
    }
};
