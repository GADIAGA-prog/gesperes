<?php

namespace App\Services;

use App\Enums\EtapeDossier;
use App\Enums\StatutSuiviDossier;
use App\Models\SuiviDossier;
use App\Models\SuiviDossierEtape;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logique métier du suivi des dossiers : circuit de traitement (réception,
 * transmissions successives, clôture) et calcul du respect du délai.
 *
 * Les méthodes de calcul du délai sont pures (sans accès base) afin d'être
 * testables unitairement (cf. tests/Unit/SuiviDossierServiceTest.php).
 */
class SuiviDossierService
{
    /** Date limite de traitement = date de réception + délai accordé (jours). */
    public function dateLimite(SuiviDossier $dossier): ?Carbon
    {
        if (! $dossier->date_reception || ! $dossier->delai_jours) {
            return null;
        }

        return $dossier->date_reception->copy()->addDays($dossier->delai_jours);
    }

    /**
     * Le délai de traitement est-il dépassé ?
     * - Dossier traité/clos : on compare la date de traitement effective à l'échéance.
     * - Dossier en cours     : on compare la date du jour à l'échéance.
     */
    public function estEnRetard(SuiviDossier $dossier): bool
    {
        $limite = $this->dateLimite($dossier);
        if (! $limite) {
            return false;
        }

        $reference = $dossier->statut?->estTermine() && $dossier->date_traitement
            ? $dossier->date_traitement
            : Carbon::today();

        return $reference->greaterThan($limite);
    }

    /**
     * Jours restants avant l'échéance (négatif si dépassé), null si pas de délai.
     * Pour un dossier traité, renvoie l'écart figé entre traitement et échéance.
     */
    public function joursRestants(SuiviDossier $dossier): ?int
    {
        $limite = $this->dateLimite($dossier);
        if (! $limite) {
            return null;
        }

        $reference = ($dossier->statut?->estTermine() && $dossier->date_traitement
            ? $dossier->date_traitement
            : Carbon::today())->copy()->startOfDay();

        $limite = $limite->copy()->startOfDay();

        // Magnitude absolue puis signe explicite (robuste Carbon 2/3).
        $jours = (int) $reference->diffInDays($limite, true);

        // Positif = encore dans les temps ; négatif = nombre de jours de retard.
        return $reference->lessThanOrEqualTo($limite) ? $jours : -$jours;
    }

    /**
     * Crée un dossier et enregistre sa première étape (réception) dans l'historique.
     */
    public function creer(array $data, ?int $userId = null): SuiviDossier
    {
        return DB::transaction(function () use ($data, $userId) {
            $dossier = SuiviDossier::create([
                'reference_bordereau' => $data['reference_bordereau'],
                'structure_id'        => $data['structure_id'],
                'nature_id'           => $data['nature_id'] ?? null,
                'objet'               => $data['objet'] ?? null,
                'etape'               => $data['etape'] ?? EtapeDossier::RECEPTION->value,
                'statut'              => $data['statut'] ?? StatutSuiviDossier::EN_COURS->value,
                'service_actuel_id'   => $data['service_actuel_id'] ?? $data['structure_id'],
                'agent_actuel_id'     => $data['agent_actuel_id'] ?? null,
                'date_reception'      => $data['date_reception'],
                'delai_jours'         => $data['delai_jours'] ?? 0,
                'date_traitement'     => $data['date_traitement'] ?? null,
                'observation'         => $data['observation'] ?? null,
                'created_by'          => $userId,
            ]);

            $this->journaliser($dossier, [
                'etape'          => $dossier->etape->value,
                'service_id'     => $dossier->service_actuel_id,
                'agent_id'       => $dossier->agent_actuel_id,
                'date_mouvement' => $dossier->date_reception->toDateString(),
                'commentaire'    => 'Réception du dossier',
            ], $userId);

            return $dossier;
        });
    }

    /**
     * Transmet le dossier à une nouvelle étape / un nouveau service / agent et
     * historise le mouvement. Met à jour la localisation courante du dossier.
     */
    public function transmettre(SuiviDossier $dossier, array $data, ?int $userId = null): SuiviDossierEtape
    {
        return DB::transaction(function () use ($dossier, $data, $userId) {
            $dossier->update([
                'etape'             => $data['etape'],
                'service_actuel_id' => $data['service_id'] ?? $dossier->service_actuel_id,
                'agent_actuel_id'   => $data['agent_id'] ?? null,
            ]);

            return $this->journaliser($dossier, $data, $userId);
        });
    }

    /**
     * Clôture le dossier : passe en étape « clôturé », statut « traité » et fixe
     * la date de traitement (qui fige le calcul du respect du délai).
     */
    public function cloturer(SuiviDossier $dossier, array $data, ?int $userId = null): SuiviDossier
    {
        return DB::transaction(function () use ($dossier, $data, $userId) {
            $dateTraitement = $data['date_traitement'] ?? Carbon::today()->toDateString();

            $dossier->update([
                'etape'           => EtapeDossier::CLOS->value,
                'statut'          => StatutSuiviDossier::TRAITE->value,
                'date_traitement' => $dateTraitement,
            ]);

            $this->journaliser($dossier, [
                'etape'          => EtapeDossier::CLOS->value,
                'service_id'     => $dossier->service_actuel_id,
                'agent_id'       => $dossier->agent_actuel_id,
                'date_mouvement' => $dateTraitement,
                'commentaire'    => $data['commentaire'] ?? 'Clôture du dossier',
            ], $userId);

            return $dossier;
        });
    }

    /** Enregistre une ligne d'historique pour un dossier. */
    private function journaliser(SuiviDossier $dossier, array $data, ?int $userId): SuiviDossierEtape
    {
        return $dossier->etapes()->create([
            'etape'          => $data['etape'],
            'service_id'     => $data['service_id'] ?? null,
            'agent_id'       => $data['agent_id'] ?? null,
            'date_mouvement' => $data['date_mouvement'] ?? Carbon::today()->toDateString(),
            'commentaire'    => $data['commentaire'] ?? null,
            'created_by'     => $userId,
        ]);
    }
}
