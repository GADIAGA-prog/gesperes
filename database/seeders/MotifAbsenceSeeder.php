<?php

namespace Database\Seeders;

use App\Enums\CategorieAbsence;
use App\Models\MotifAbsence;
use Illuminate\Database\Seeder;

/**
 * Nomenclature des motifs d'absence, classés par nature
 * (autorisation, congé, justifiée, injustifiée).
 */
class MotifAbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $motifs = [
            // Congé annuel — imputé sur les 30 jours
            ['CONGE_ANN', 'Congé annuel', CategorieAbsence::CONGE],

            // Autorisations d'absence — imputées sur les 10 jours (dépassement → congé)
            ['AUTO',      "Autorisation d'absence", CategorieAbsence::AUTORISATION],
            ['EVEN_FAM',  'Événement familial', CategorieAbsence::AUTORISATION],
            ['MARIAGE',   'Mariage', CategorieAbsence::AUTORISATION],
            ['DECES_FAM', 'Décès d\'un proche', CategorieAbsence::AUTORISATION],

            // Absences justifiées — non imputées sur les quotas
            ['MALADIE',   'Maladie (certificat médical)', CategorieAbsence::JUSTIFIEE],
            ['MATERNITE', 'Congé de maternité', CategorieAbsence::JUSTIFIEE],
            ['MISSION',   'Mission de service', CategorieAbsence::JUSTIFIEE],
            ['FORMATION', 'Formation / stage', CategorieAbsence::JUSTIFIEE],

            // Absence injustifiée — relevée pour mesures disciplinaires
            ['INJUST',    'Absence injustifiée', CategorieAbsence::INJUSTIFIEE],
        ];

        foreach ($motifs as [$code, $libelle, $categorie]) {
            MotifAbsence::firstOrCreate(
                ['code' => $code],
                ['libelle' => $libelle, 'categorie' => $categorie->value, 'actif' => true]
            );
        }

        $this->command->info('✓ Motifs d\'absence (' . count($motifs) . ').');
    }
}
