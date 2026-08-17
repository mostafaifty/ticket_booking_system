<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Administrator Account
        User::updateOrCreate(
            ['email' => 'admin@railway.com'],
            [
                'name' => 'System Administrator',
                'role' => User::ROLE_ADMIN,
                'phone' => '+8801700000001',
                'password' => Hash::make('password'),
            ]
        );

        // Default Passenger Account
        User::updateOrCreate(
            ['email' => 'passenger@railway.com'],
            [
                'name' => 'Demo Passenger',
                'role' => User::ROLE_PASSENGER,
                'phone' => '+8801700000002',
                'password' => Hash::make('password'),
            ]
        );
    }
}
