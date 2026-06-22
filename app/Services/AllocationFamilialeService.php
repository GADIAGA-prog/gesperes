<?php

namespace App\Services;

/**
 * Calcul indicatif de l'allocation familiale selon le nombre d'enfants.
 * Barème paramétré dans config/gesperes.php (NON contractuel).
 */
class AllocationFamilialeService
{
    public function calculer(int $nombreEnfants): float
    {
        $montant = (float) config('gesperes.allocation_familiale.montant_par_enfant', 0);
        $max = (int) config('gesperes.allocation_familiale.nombre_max_enfants', 6);
        $enfantsPris = max(0, min($nombreEnfants, $max));
        return round($enfantsPris * $montant, 2);
    }
}
