<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdministrator();
        $this->createManager();
        $this->createActiveUsers();
        $this->createInactiveUser();
    }

    private function createAdministrator(): void
    {
        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'name' => 'Admin User',
            'role' => 'administrator',
            'active' => true,
        ]);
    }

    private function createManager(): void
    {
        User::create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'name' => 'Manager User',
            'role' => 'manager',
            'active' => true,
        ]);
    }

    private function createActiveUsers(): void
    {
        $users = [
            ['email' => 'john@example.com', 'name' => 'John Doe'],
            ['email' => 'jane@example.com', 'name' => 'Jane Smith'],
            ['email' => 'alice@example.com', 'name' => 'Alice Brown'],
        ];

        foreach ($users as $userData) {
            User::create([
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'name' => $userData['name'],
                'role' => 'user',
                'active' => true,
            ]);
        }
    }

    private function createInactiveUser(): void
    {
        User::create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'name' => 'Inactive User',
            'role' => 'user',
            'active' => false,
        ]);
    }
}
