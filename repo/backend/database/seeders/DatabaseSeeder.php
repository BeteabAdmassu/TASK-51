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
        $users = [
            ['username' => 'admin01', 'password' => 'Admin12345!', 'role' => 'admin'],
            ['username' => 'rider01', 'password' => 'Rider12345!', 'role' => 'rider'],
            ['username' => 'rider02', 'password' => 'Rider12345!', 'role' => 'rider'],
            ['username' => 'driver01', 'password' => 'Driver1234!', 'role' => 'driver'],
            ['username' => 'driver02', 'password' => 'Driver1234!', 'role' => 'driver'],
            ['username' => 'fleet01', 'password' => 'Fleet12345!', 'role' => 'fleet_manager'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['username' => $user['username']],
                [
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'last_login_at' => null,
                ]
            );
        }
    }
}
