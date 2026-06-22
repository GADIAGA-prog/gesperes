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
 * date_naissance, emploi, categorie, region, province, commune, etablissement,
 * nombre_enfants, situation_matrimoniale.
 *
 * Le mapping emploi/categorie se fait par libellé/code (insensible à la casse).
 * Les lignes invalides sont collectées dans $erreurs sans interrompre l'import.
 */
class AgentsImport implements ToCollection, WithHeadingRow
{
    public int $importes = 0;
    public array $erreurs = [];

    public function __construct(
        private AgentService $service,
        private ?int $userId = null,
    ) {}

    public function collection(Collection $rows): void
    {
        $emplois = Emploi::pluck('id', 'libelle')->mapWithKeys(fn ($id, $lib) => [mb_strtolower($lib) => $id]);
        $categories = Categorie::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [mb_strtolower($code) => $id]);

        foreach ($rows as $i => $row) {
            $ligne = $i + 2; // +1 heading, +1 base 0
            $matricule = trim((string) ($row['matricule'] ?? ''));

            if ($matricule === '') {
                continue; // ligne vide
            }

            if (Agent::where('matricule', $matricule)->exists()) {
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
                    'region'      => $row['region'] ?? null,
                    'province'    => $row['province'] ?? null,
                    'commune'     => $row['commune'] ?? null,
                    'etablissement' => $row['etablissement'] ?? null,
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
