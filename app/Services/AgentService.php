<?php

namespace App\Services;

use App\Enums\LieuExercice;
use App\Models\Agent;
use App\Models\Emploi;
use Illuminate\Support\Str;

/**
 * Orchestration métier de l'agent : applique les règles automatiques
 * (clé en majuscule, date de retraite, allocation familiale, lieu d'exercice,
 * volume horaire des enseignants) avant persistance.
 */
class AgentService
{
    public function __construct(
        private RetraiteService $retraite,
        private AllocationFamilialeService $allocation,
    ) {}

    /** Normalise et complète les données issues du formulaire. */
    public function preparer(array $data): array
    {
        // R2 : la clé est alphabétique en majuscule
        if (! empty($data['cle'])) {
            $data['cle'] = strtoupper(preg_replace('/[^A-Za-z]/', '', $data['cle']));
        }

        // R3 : sexe contrôlé en amont par le FormRequest

        // Détermination de l'emploi (enseignant ou non)
        $emploi = ! empty($data['emploi_id']) ? Emploi::find($data['emploi_id']) : null;

        // R8 : lieu d'exercice déduit de l'emploi
        $data['lieu_exercice'] = ($emploi && $emploi->enseignant)
            ? LieuExercice::EN_CLASSE->value
            : LieuExercice::AU_BUREAU->value;

        // R10 : volumes horaires réservés aux enseignants
        if (! ($emploi && $emploi->enseignant)) {
            $data['volume_horaire_du'] = null;
            $data['volume_horaire_assure'] = null;
            $data['type_enseignement_id'] = $data['type_enseignement_id'] ?? null;
        } else {
            // valeur par défaut si non renseignée
            $data['volume_horaire_du'] = $data['volume_horaire_du']
                ?? $emploi->volume_horaire_defaut
                ?? config('gesperes.volume_horaire_defaut');
        }

        // R7 : date de retraite calculée automatiquement
        if (! empty($data['date_naissance'])) {
            $codeCat = $this->codeCategorie($data);
            $data['date_retraite'] = $this->retraite
                ->dateRetraite(\Carbon\Carbon::parse($data['date_naissance']), $codeCat)
                ?->toDateString();
        }

        // R9 : allocation familiale calculée selon le nombre d'enfants
        $data['allocation_familiale'] = $this->allocation->calculer((int) ($data['nombre_enfants'] ?? 0));

        // Affectation géographique : on conserve les libellés texte (export, PDF, filtres,
        // import Excel) en les synchronisant depuis les clés étrangères quand elles sont fournies.
        if (! empty($data['region_id'])) {
            $data['region'] = \App\Models\Region::find($data['region_id'])?->libelle;
        }
        if (! empty($data['province_id'])) {
            $data['province'] = \App\Models\Province::find($data['province_id'])?->libelle;
        }
        if (! empty($data['localite_id'])) {
            $data['commune'] = \App\Models\Localite::find($data['localite_id'])?->libelle;
        }

        return $data;
    }

    public function creer(array $data, ?int $userId = null): Agent
    {
        $data = $this->preparer($data);
        $data['created_by'] = $userId;
        return Agent::create($data);
    }

    public function mettreAJour(Agent $agent, array $data): Agent
    {
        $agent->update($this->preparer($data));
        return $agent;
    }

    private function codeCategorie(array $data): ?string
    {
        if (empty($data['categorie_id'])) {
            return null;
        }
        return \App\Models\Categorie::find($data['categorie_id'])?->code;
    }
}
