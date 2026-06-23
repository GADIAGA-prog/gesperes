<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('── GesPerES — Initialisation de la base de données ──');

        $this->call([
            RoleSeeder::class,        // 1. Permissions & rôles
            ReferentielSeeder::class, // 2. Nomenclatures de base
            GeographieSeeder::class,  // 3. Découpage administratif (régions, provinces, chefs-lieux)
            MotifAbsenceSeeder::class,// 4. Motifs d'absence (module présence/congés)
            IndemniteBaremeSeeder::class, // 5. Barèmes d'indemnités (décret 2014-427, données GESPER)
            NatureDossierSeeder::class,   // 6. Natures de dossier (suivi des dossiers)
            UserSeeder::class,        // 7. Compte super-admin (dépend des rôles)
        ]);

        $this->command->info('✓ Base de données initialisée avec succès.');
    }
}
