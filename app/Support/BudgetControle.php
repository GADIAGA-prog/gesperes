<?php

namespace App\Support;

use App\Models\Activite;

/**
 * Contrôles de cohérence budgétaire d'une activité :
 *  - totaux des lignes (AE / CP) ;
 *  - ventilation trimestrielle (doit totaliser 100 %).
 */
final class BudgetControle
{
    /** Tolérance sur la somme des fractions trimestrielles. */
    public const TOLERANCE = 0.01;

    /**
     * @return array{total_ae:float,total_cp:float,somme_trimestres:float,
     *   trimestres_renseignes:bool,trimestres_ok:bool,coherent:bool,messages:array<string>}
     */
    public static function pour(Activite $activite): array
    {
        $totalAe = (float) $activite->lignes->sum('montant_ae');
        $totalCp = (float) $activite->lignes->sum('montant_cp');

        $somme = (float) $activite->trimestre_1 + (float) $activite->trimestre_2
            + (float) $activite->trimestre_3 + (float) $activite->trimestre_4;

        $renseignes = $somme > 0;
        $trimestresOk = ! $renseignes || abs($somme - 1.0) <= self::TOLERANCE;

        $messages = [];
        if ($renseignes && ! $trimestresOk) {
            $messages[] = 'La ventilation trimestrielle totalise ' . round($somme * 100) . ' % au lieu de 100 %.';
        }
        if ($activite->lignes->isEmpty()) {
            $messages[] = 'Aucune ligne budgétaire saisie.';
        }
        if ($totalCp > $totalAe && $totalAe > 0) {
            $messages[] = 'Le crédit de paiement (CP) dépasse l\'autorisation d\'engagement (AE).';
        }

        return [
            'total_ae'              => $totalAe,
            'total_cp'              => $totalCp,
            'somme_trimestres'      => $somme,
            'trimestres_renseignes' => $renseignes,
            'trimestres_ok'         => $trimestresOk,
            'coherent'              => $trimestresOk && $activite->lignes->isNotEmpty(),
            'messages'              => $messages,
        ];
    }
}
