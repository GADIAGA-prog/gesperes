<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('── GesPerES — Initialisation de la base de données ──');

        $this->call([
            RoleSeeder::class,       // 1. Permissions & rôles
            ReferentielSeeder::class, // 2. Nomenclatures de base
            UserSeeder::class,        // 3. Compte super-admin (dépend des rôles)
        ]);

        $this->command->info('✓ Base de données initialisée avec succès.');
    }
}
