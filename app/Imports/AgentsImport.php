<?php

namespace App\Imports;

use App\Models\Agent;
use App\Models\Categorie;
use App\Models\Emploi;
use App\Services\AgentService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import en masse d'agents depuis un fichier Excel/CSV.
 * Les en-têtes attendus (ligne 1) : matricule, cle, nom, prenoms, sexe,
 * date_naissance, emploi, categorie, niveau_1, niveau_2, niveau_3, niveau_4,
 * structure, service, nombre_enfants, situation_matrimoniale.
 *
 * Le rattachement est résolu à partir des colonnes de cascade (niveau_1 … niveau_4,
 * structure, service) : on identifie la structure dont le chemin hiérarchique se
 * termine par les libellés fournis. La région/province/commune sont alors déduites
 * de la structure par AgentService (jamais saisies en texte libre).
 *
 * Le mapping emploi/categorie se fait par libellé/code (insensible à la casse).
 * Les lignes invalides sont collectées dans $erreurs sans interrompre l'import.
 */
class AgentsImport implements ToCollection, WithHeadingRow
{
    public int $importes = 0;
    public int $ignores = 0;
    public array $erreurs = [];

    /** Matricules déjà rencontrés dans le fichier courant (anti-doublon intra-fichier). */
    private array $vus = [];

    /** Chemins hiérarchiques des structures en minuscules : id => [niveaux]. */
    private array $cheminsStructures = [];

    public function __construct(
        private AgentService $service,
        private ?int $userId = null,
    ) {}

    public function collection(Collection $rows): void
    {
        $emplois = Emploi::pluck('id', 'libelle')->mapWithKeys(fn ($id, $lib) => [mb_strtolower($lib) => $id]);
        $categories = Categorie::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [mb_strtolower($code) => $id]);

        // Pré-calcul des chemins de structures (une seule requête) pour résoudre la cascade.
        $this->cheminsStructures = array_map(
            fn (array $niveaux) => array_map(fn ($n) => mb_strtolower(trim((string) $n)), $niveaux),
            \App\Models\Structure::cheminsParId(),
        );

        foreach ($rows as $i => $row) {
            $ligne = $i + 2; // +1 heading, +1 base 0
            $matricule = trim((string) ($row['matricule'] ?? ''));

            if ($matricule === '') {
                continue; // ligne vide
            }

            // Anti-doublon intra-fichier (même matricule présent deux fois).
            $cle = mb_strtoupper($matricule);
            if (isset($this->vus[$cle])) {
                $this->ignores++;
                $this->erreurs[] = "Ligne {$ligne} : matricule {$matricule} en double dans le fichier, ignoré.";
                continue;
            }
            $this->vus[$cle] = true;

            // Anti-doublon en base.
            if (Agent::where('matricule', $matricule)->exists()) {
                $this->ignores++;
                $this->erreurs[] = "Ligne {$ligne} : matricule {$matricule} déjà existant, ignoré.";
                continue;
            }

            try {
                $this->service->creer([
                    'matricule'   => $matricule,
                    'cle'         => $row['cle'] ?? null,
                    'nom'         => $row['nom'] ?? null,
                    'prenoms'     => $row['prenoms'] ?? null,
                    'sexe'        => $this->sexe($row['sexe'] ?? null),
                    'date_naissance' => $this->date($row['date_naissance'] ?? null),
                    'emploi_id'   => $emplois[mb_strtolower(trim((string) ($row['emploi'] ?? '')))] ?? null,
                    'categorie_id' => $categories[mb_strtolower(trim((string) ($row['categorie'] ?? '')))] ?? null,
                    'structure_id' => $this->resoudreStructure($row),
                    'nombre_enfants' => (int) ($row['nombre_enfants'] ?? 0),
                    'situation_matrimoniale' => $row['situation_matrimoniale'] ?? null,
                    'statut_dossier' => 'incomplet',
                ], $this->userId);

                $this->importes++;
            } catch (\Throwable $e) {
                $this->erreurs[] = "Ligne {$ligne} : {$e->getMessage()}";
            }
        }
    }

    /**
     * Résout la structure de rattachement à partir des colonnes de cascade.
     * On lit niveau_1 … niveau_4 (sinon, à défaut, structure puis service) pour
     * reconstituer le suffixe du chemin, puis on cherche la structure dont le
     * chemin hiérarchique se termine exactement par ces libellés. Retourne l'id
     * uniquement si la correspondance est unique (sinon null, pas de devinette).
     */
    private function resoudreStructure($row): ?int
    {
        $niveaux = array_values(array_filter([
            $row['niveau_1'] ?? null,
            $row['niveau_2'] ?? null,
            $row['niveau_3'] ?? null,
            $row['niveau_4'] ?? null,
        ], fn ($v) => trim((string) $v) !== ''));

        // À défaut de niveaux, on tente structure (parente) puis service (feuille).
        if (empty($niveaux)) {
            $niveaux = array_values(array_filter([
                $row['structure'] ?? null,
                $row['service'] ?? null,
            ], fn ($v) => trim((string) $v) !== ''));
        }

        if (empty($niveaux)) {
            return null;
        }

        $cible = array_map(fn ($v) => mb_strtolower(trim((string) $v)), $niveaux);
        $n = count($cible);

        $correspondances = [];
        foreach ($this->cheminsStructures as $id => $chemin) {
            if (count($chemin) >= $n && array_slice($chemin, -$n) === $cible) {
                $correspondances[] = $id;
            }
        }

        // Plusieurs candidats : on privilégie une correspondance de chemin complet (même longueur).
        if (count($correspondances) > 1) {
            $exacts = array_values(array_filter(
                $correspondances,
                fn ($id) => count($this->cheminsStructures[$id]) === $n,
            ));
            if (count($exacts) === 1) {
                return (int) $exacts[0];
            }
        }

        return count($correspondances) === 1 ? (int) $correspondances[0] : null;
    }

    private function sexe(?string $valeur): ?string
    {
        $v = mb_strtoupper(trim((string) $valeur));
        return match ($v) {
            'M', 'MASCULIN', 'H', 'HOMME' => 'M',
            'F', 'FÉMININ', 'FEMININ', 'FEMME' => 'F',
            default => null,
        };
    }

    private function date($valeur): ?string
    {
        if (empty($valeur)) {
            return null;
        }
        try {
            if (is_numeric($valeur)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valeur)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($valeur)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
