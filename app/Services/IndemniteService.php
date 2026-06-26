<?php

namespace App\Services;

use App\Enums\LieuExercice;
use App\Enums\ModeIndemnite;
use App\Models\Agent;
use App\Models\BaremeAstreinte;
use App\Models\BaremeLogement;
use App\Models\BaremeSpecifique;
use App\Models\BaremeTechnicite;
use App\Models\Indemnite;

/**
 * Moteur de calcul des indemnités à partir des barèmes du décret 2014-427
 * (données GESPER). Aucun taux n'est codé en dur.
 *
 * Règles appliquées (cf. fichiers GESPER) :
 *  - Logement   : barème (catégorie × enseignant × en classe/au bureau).
 *  - Technicité : barème par échelle, codée « catégorie + n° d'échelle »
 *                 (A + ECHL1 = A1, P + ECHLA = PA).
 *  - Astreinte  : barème (emploi × zone) — nécessite la zone de l'agent.
 *  - Spécifique : barème (emploi × zone) — nécessite la zone de l'agent.
 *
 * Les barèmes (petites tables) sont chargés une fois et mémoïsés sur l'instance
 * pour éviter toute requête par agent lors des calculs de masse (budget).
 */
class IndemniteService
{
    private ?array $logementMap = null;
    private ?array $astreinteMap = null;
    private ?array $specifiqueMap = null;
    private ?array $techniciteMap = null;
    private ?array $zonesStructure = null;

    public function __construct(private AllocationFamilialeService $allocation) {}

    /** Montant mensuel d'une indemnité pour un agent donné (interface générique UI). */
    public function calculer(Agent $agent, Indemnite $indemnite): float
    {
        // Indemnités pilotées par une règle propre (et non par le montant du référentiel) :
        //  - responsabilité = indemnité de la fonction de l'agent ;
        //  - allocation familiale = calcul selon le nombre d'enfants.
        return match ($indemnite->code) {
            'RESP'  => (float) ($agent->fonction?->indemnite_responsabilite ?? 0),
            'ALLOC' => $this->allocation->calculer((int) ($agent->nombre_enfants ?? 0)),
            default => match ($indemnite->mode) {
                ModeIndemnite::MONTANT_FIXE => (float) $indemnite->valeur,
                ModeIndemnite::POURCENTAGE  => round((float) $indemnite->valeur / 100 * $this->base($agent), 2),
                ModeIndemnite::BAREME       => $this->baremePourCode($agent, (string) $indemnite->bareme) ?? 0.0,
            },
        };
    }

    /** Calcule toutes les indemnités actives applicables à un agent. */
    public function pourAgent(Agent $agent): array
    {
        return Indemnite::where('actif', true)->orderBy('libelle')->get()
            ->map(fn (Indemnite $i) => [
                'indemnite' => $i,
                'montant'   => $this->calculer($agent, $i),
            ])
            ->all();
    }

    /* ───────── Indemnités barème, par type (utilisées par le budget) ───────── */

    /** Logement = barème(catégorie, enseignant, au bureau/en classe). Toujours calculable. */
    public function logement(Agent $agent): float
    {
        $cat = $agent->categorie?->code;
        if (! $cat) {
            return 0.0;
        }
        $enseignant = $agent->emploi?->enseignant ? '1' : '0';
        $enClasse   = $agent->lieu_exercice === LieuExercice::EN_CLASSE ? '1' : '0';

        return $this->mapLogement()[$cat . '|' . $enseignant . '|' . $enClasse] ?? 0.0;
    }

    /** Technicité = barème par échelle (code « catégorie + n° d'échelle »). Toujours calculable. */
    public function technicite(Agent $agent): float
    {
        $cat = $agent->categorie?->code;
        $ech = $agent->echelle?->code;
        if (! $cat || ! $ech) {
            return 0.0;
        }

        return $this->mapTechnicite()[$cat . str_replace('ECHL', '', $ech)] ?? 0.0;
    }

    /**
     * Astreinte = barème(emploi, zone). Renvoie null si la zone de l'agent n'est
     * pas déterminable (décentralisé non rattaché à une localité) — le budget
     * retombe alors sur la valeur réelle attribuée.
     */
    public function astreinte(Agent $agent): ?float
    {
        return $this->emploiZone($agent, $this->mapAstreinte());
    }

    /** Spécifique harmonisée = barème(emploi, zone). Voir astreinte() pour le null. */
    public function specifique(Agent $agent): ?float
    {
        return $this->emploiZone($agent, $this->mapSpecifique());
    }

    /**
     * Zone d'astreinte/résidence de l'agent :
     *  1. la zone de sa localité si elle est renseignée ;
     *  2. sinon la zone portée par sa direction (régionale/provinciale) via la cascade ;
     *  3. sinon, administration centrale (Ouagadougou) = urbaine.
     */
    public function zonePour(Agent $agent): string
    {
        if ($zone = $agent->localite?->zone?->code) {
            return $zone;
        }

        return $this->mapZonesStructure()[$agent->structure_id] ?? 'urbaine';
    }

    /* ───────── Internes ───────── */

    private function emploiZone(Agent $agent, array $map): ?float
    {
        $emploi = $agent->emploi?->code;
        if (! $emploi) {
            return null;
        }

        // Zone connue mais emploi absent du barème = pas d'indemnité (0).
        return $map[$emploi . '|' . $this->zonePour($agent)] ?? 0.0;
    }

    /** Dispatch barème générique (UI : calculer()). */
    private function baremePourCode(Agent $agent, string $bareme): ?float
    {
        return match ($bareme) {
            'logement'   => $this->logement($agent),
            'technicite' => $this->technicite($agent),
            'astreinte'  => $this->astreinte($agent),
            'specifique' => $this->specifique($agent),
            default      => null,
        };
    }

    /** Base de calcul pour les indemnités proportionnelles : le salaire indiciaire. */
    private function base(Agent $agent): float
    {
        return (float) ($agent->indice?->salaire_indiciaire ?? 0);
    }

    /* ───────── Barèmes mémoïsés (chargés une seule fois) ───────── */

    private function mapLogement(): array
    {
        return $this->logementMap ??= BaremeLogement::where('actif', true)->get()
            ->mapWithKeys(fn ($r) => [
                $r->categorie_code . '|' . ($r->enseignant ? '1' : '0') . '|' . ($r->en_classe ? '1' : '0') => (float) $r->montant,
            ])->all();
    }

    private function mapAstreinte(): array
    {
        return $this->astreinteMap ??= BaremeAstreinte::where('actif', true)->get()
            ->mapWithKeys(fn ($r) => [$r->emploi_code . '|' . $r->zone_code => (float) $r->montant])->all();
    }

    private function mapSpecifique(): array
    {
        return $this->specifiqueMap ??= BaremeSpecifique::where('actif', true)->get()
            ->mapWithKeys(fn ($r) => [$r->emploi_code . '|' . $r->zone_code => (float) $r->montant])->all();
    }

    private function mapTechnicite(): array
    {
        return $this->techniciteMap ??= BaremeTechnicite::where('actif', true)->get()
            ->mapWithKeys(fn ($r) => [$r->echelle_code => (float) $r->montant])->all();
    }

    /** Carte structure_id => zone (cascade), mémoïsée pour les calculs de masse. */
    private function mapZonesStructure(): array
    {
        return $this->zonesStructure ??= \App\Models\Structure::zonesParStructure();
    }
}
