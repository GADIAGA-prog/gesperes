<?php

namespace App\Services;

/**
 * Calcule tous les éléments de rémunération dérivés de l'indice, d'après les
 * formules du barème officiel (fichier « Bon_annexe … », feuille barème).
 * Aucun taux n'est codé en dur : tout vient de config/grille.php.
 *
 *   Brut annuel      = indice × point_annuel
 *   Solde indiciaire = round(indice × point_annuel / mois_par_an)
 *   CARFO (8 %)      = round(solde × carfo_taux)
 *   Net mensuel      = solde − CARFO
 *   Résidence (10 %) = solde × residence_taux
 *   Base imposable   = tronc(net + résidence − solde × abattement_taux)
 *   IUTS             = barème progressif sur la base, réduit selon les charges
 */
class GrilleIndiciaireService
{
    private float $point;
    private int $mois;

    public function __construct()
    {
        $this->point = (float) config('grille.point_annuel');
        $this->mois  = (int) config('grille.mois_par_an', 12);
    }

    public function brutAnnuel(int $indice): float
    {
        return $indice * $this->point;
    }

    public function soldeIndiciaire(int $indice): float
    {
        return $this->mois === 0 ? 0.0 : round($indice * $this->point / $this->mois);
    }

    public function carfo(int $indice): float
    {
        return round($this->soldeIndiciaire($indice) * (float) config('grille.carfo_taux'));
    }

    public function netMensuel(int $indice): float
    {
        return $this->soldeIndiciaire($indice) - $this->carfo($indice);
    }

    public function residence(int $indice): float
    {
        return $this->soldeIndiciaire($indice) * (float) config('grille.residence_taux');
    }

    public function baseImposable(int $indice): float
    {
        $solde = $this->soldeIndiciaire($indice);
        $base  = $this->netMensuel($indice) + $this->residence($indice)
            - $solde * (float) config('grille.abattement_taux');

        return $this->tronquer($base, (int) config('grille.base_imposable_arrondi', 100));
    }

    /** Impôt (IUTS) mensuel pour un nombre de personnes à charge donné. */
    public function iuts(int $indice, int $charges = 0): float
    {
        $base = $this->baseImposable($indice);

        $impot = 0.0;
        foreach (config('grille.iuts_tranches', []) as $t) {
            if ($base > $t['seuil']) {
                $impot = ($base - $t['seuil']) * $t['taux'] + $t['fixe'];
                break;
            }
        }

        return round($impot * $this->facteurCharges($charges));
    }

    /** Détail complet pour un indice (et un nombre de charges). */
    public function detail(int $indice, int $charges = 0): array
    {
        return [
            'indice'           => $indice,
            'brut_annuel'      => $this->brutAnnuel($indice),
            'solde_indiciaire' => $this->soldeIndiciaire($indice),
            'carfo'            => $this->carfo($indice),
            'net_mensuel'      => $this->netMensuel($indice),
            'residence'        => $this->residence($indice),
            'base_imposable'   => $this->baseImposable($indice),
            'iuts'             => $this->iuts($indice, $charges),
        ];
    }

    /** Facteur de réduction de l'IUTS selon le nombre de personnes à charge (borné). */
    private function facteurCharges(int $charges): float
    {
        $facteurs = config('grille.charges_facteurs', [1.0]);
        $charges = max(0, min($charges, count($facteurs) - 1));

        return (float) $facteurs[$charges];
    }

    /** Troncature vers zéro au multiple inférieur (équivalent TRUNC(x, -2)). */
    private function tronquer(float $valeur, int $pas): float
    {
        if ($pas <= 0) {
            return $valeur;
        }

        return floor($valeur / $pas) * $pas;
    }
}
