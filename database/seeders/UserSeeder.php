<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un utilisateur admin de test
        User::create([
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone_number' => '+1234567890',
            'address' => '123 Admin Street, City, Country',
            'password' => Hash::make('password123'),
        ]);

        // Créer un utilisateur régulier de test
        User::create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+0987654321',
            'address' => '456 Test Avenue, Town, Country',
            'password' => Hash::make('password123'),
        ]);

        // Créer 10 utilisateurs supplémentaires
        User::factory()->count(10)->create();
    }
}