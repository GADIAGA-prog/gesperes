<?php

namespace Database\Seeders;

use App\Models\Fonction;
use Illuminate\Database\Seeder;

/**
 * Aligne l'indemnité de responsabilité des fonctions RÉELLEMENT utilisées par les
 * agents sur les montants du décret 2014-427 (ligne du décret citée en commentaire).
 *
 * Séparé du référentiel d'import (création seule) car il modifie des fonctions
 * existantes : chaque correspondance est explicite et validée, jamais devinée.
 * Idempotent.
 */
class CorrigerIndemnitesResponsabiliteSeeder extends Seeder
{
    /** code fonction => [montant décret, ligne du décret]. */
    private const CORRECTIONS = [
        'PROV'  => [12000, 'Proviseur de lycée'],
        '2'     => [18500, 'Directeur central (DRH, DCPM…)'],
        'SG'    => [60000, 'Secrétaires généraux des institutions et des ministères'],
        'DIREG' => [28000, 'Directeur Régional'],
        'CENS'  => [10500, "Censeur d'établissement secondaire"],
        '1'     => [10500, 'Chef de service nommé par arrêté'],
    ];

    public function run(): void
    {
        $n = 0;
        foreach (self::CORRECTIONS as $code => [$montant]) {
            // (string) : les codes « 1 »/« 2 » sont des clés numériques converties
            // en entiers par PHP — forcer la chaîne évite une comparaison SQL erronée.
            $maj = Fonction::where('code', (string) $code)
                ->where('indemnite_responsabilite', '!=', $montant)
                ->update(['indemnite_responsabilite' => $montant]);
            $n += $maj;
        }

        $this->command?->info("✓ Indemnités de responsabilité alignées sur le décret 2014-427 ({$n} fonction(s) modifiée(s)).");
    }
}
