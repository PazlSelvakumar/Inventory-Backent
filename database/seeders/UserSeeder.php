<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'), // Default password
        //     'role' => 'admin',
        // ]);

        // // Manager User
        // User::create([
        //     'name' => 'Manager User',
        //     'email' => 'manager@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'manager',
        // ]);

        // // Normal User
        // User::create([
        //     'name' => 'Regular User',
        //     'email' => 'user@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'user',
        // ]);
    }
}
