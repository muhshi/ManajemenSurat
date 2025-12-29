<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Kepala Dinas', 'email' => 'kepala@example.com'],
            ['name' => 'Sekretaris', 'email' => 'sekretaris@example.com'],
            ['name' => 'Kabid Umum', 'email' => 'kabid.umum@example.com'],
            ['name' => 'Kasubag Arsip', 'email' => 'kasubag.arsip@example.com'],
            ['name' => 'Staff Administrasi', 'email' => 'staff1@example.com'],
            ['name' => 'Staff Kepegawaian', 'email' => 'staff2@example.com'],
            ['name' => 'Staff Keuangan', 'email' => 'staff3@example.com'],
        ];

        foreach ($users as $userData) {
            \App\Models\User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
