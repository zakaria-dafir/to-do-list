<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer tous les utilisateurs
        $users = User::all();

        // Pour chaque utilisateur, créer 3 à 8 tâches
        $users->each(function ($user) {
            Task::factory()
                ->count(rand(3, 8))
                ->create([
                    'user_id' => $user->id
                ]);
        });

        // Créer quelques tâches supplémentaires pour l'admin
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            Task::factory()
                ->count(5)
                ->create([
                    'user_id' => $admin->id,
                    'completed' => true // Toutes les tâches admin sont complétées
                ]);
        }
    }
}