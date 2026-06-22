<?php

namespace App\Services;

use App\Models\Agent;
use Carbon\CarbonImmutable;

/**
 * Calcul de la date de retraite à partir de la date de naissance et de la
 * catégorie de l'agent. L'âge légal est paramétré dans config/gesperes.php.
 */
class RetraiteService
{
    public function ageLegal(?string $codeCategorie = null): int
    {
        $parCategorie = (array) config('gesperes.retraite.par_categorie', []);
        if ($codeCategorie && isset($parCategorie[$codeCategorie])) {
            return (int) $parCategorie[$codeCategorie];
        }
        return (int) config('gesperes.retraite.age_defaut', 60);
    }

    public function dateRetraite(?\DateTimeInterface $dateNaissance, ?string $codeCategorie = null): ?CarbonImmutable
    {
        if (! $dateNaissance) {
            return null;
        }
        return CarbonImmutable::instance($dateNaissance)->addYears($this->ageLegal($codeCategorie));
    }

    public function pourAgent(Agent $agent): ?CarbonImmutable
    {
        return $this->dateRetraite($agent->date_naissance, $agent->categorie?->code);
    }

    /** L'agent part-il à la retraite dans la fenêtre d'alerte ? */
    public function procheRetraite(Agent $agent): bool
    {
        $date = $this->pourAgent($agent);
        if (! $date) {
            return false;
        }
        $mois = (int) config('gesperes.retraite.alerte_mois_avant', 24);
        return $date->isFuture() && $date->lessThanOrEqualTo(now()->addMonths($mois));
    }
}
