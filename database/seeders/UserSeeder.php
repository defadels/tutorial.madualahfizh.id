<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buat akun admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Buat akun member
        $member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => Hash::make('member123'),
            'email_verified_at' => now(),
        ]);
        $member->assignRole('member');
    }
} 