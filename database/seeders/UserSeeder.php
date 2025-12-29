<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@bpsdemak.go.id',
                'password' => 'admin123', // ganti setelah login
            ],
            [
                'name' => 'Kepala Dinas',
                'email' => 'kepala@bpsdemak.go.id',
                'password' => 'password',
            ],
            [
                'name' => 'Sekretaris',
                'email' => 'sekretaris@bpsdemak.go.id',
                'password' => 'password',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
