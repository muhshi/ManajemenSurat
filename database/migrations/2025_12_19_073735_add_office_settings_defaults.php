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
        // Insert default office settings
        \DB::table('settings')->insert([
            [
                'group' => 'office',
                'name' => 'nama_kantor',
                'locked' => false,
                'payload' => json_encode(''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'office',
                'name' => 'nama_kepala',
                'locked' => false,
                'payload' => json_encode(''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'office',
                'name' => 'nip',
                'locked' => false,
                'payload' => json_encode(''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'office',
                'name' => 'kode_kantor',
                'locked' => false,
                'payload' => json_encode(''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('settings')->where('group', 'office')->delete();
    }
};
