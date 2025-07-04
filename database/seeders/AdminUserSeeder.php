<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@nceawards.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'), // 🔒 change this in production!
                'role' => 'admin', // ensure your users table has this
            ]
        );
    }
}
