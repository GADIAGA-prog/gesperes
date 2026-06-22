<?php

namespace App\Imports;

use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelon;
use App\Models\Indice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import en masse des indices depuis un fichier Excel/CSV.
 *
 * L'indice est fonction du triplet (catégorie, classe, échelon).
 * En-têtes attendus (ligne 1) :
 *   categorie | classe | echelon | valeur | code (optionnel) | libelle (optionnel)
 *
 * - categorie / classe / echelon : reconnus par leur CODE (ou à défaut leur libellé), insensible à la casse.
 * - valeur : entier (valeur de l'indice).
 * - code : code unique de l'indice ; généré automatiquement (CATEGORIE-CLASSE-ECHELON) si absent.
 * - Si la combinaison existe déjà, sa valeur est mise à jour ; sinon l'indice est créé.
 * Les lignes invalides sont collectées dans $erreurs sans interrompre l'import.
 */
class IndicesImport implements ToCollection, WithHeadingRow
{
    public int $importes = 0;
    public int $maj = 0;
    public array $erreurs = [];

    /** @var Collection<string,int> code (minuscule) => id */
    private Collection $categories;
    private Collection $classes;
    private Collection $echelons;
    /** Index libellé => id, en repli si le code ne correspond pas. */
    private Collection $categoriesParLibelle;
    private Collection $classesParLibelle;
    private Collection $echelonsParLibelle;

    public function collection(Collection $rows): void
    {
        $this->categories = $this->indexer(Categorie::pluck('id', 'code'));
        $this->classes = $this->indexer(Classe::pluck('id', 'code'));
        $this->echelons = $this->indexer(Echelon::pluck('id', 'code'));
        $this->categoriesParLibelle = $this->indexer(Categorie::pluck('id', 'libelle'));
        $this->classesParLibelle = $this->indexer(Classe::pluck('id', 'libelle'));
        $this->echelonsParLibelle = $this->indexer(Echelon::pluck('id', 'libelle'));

        foreach ($rows as $i => $row) {
            $ligne = $i + 2; // +1 en-tête, +1 base 0

            $catRaw = trim((string) ($row['categorie'] ?? ''));
            $classeRaw = trim((string) ($row['classe'] ?? ''));
            $echelonRaw = trim((string) ($row['echelon'] ?? ''));
            $valeurRaw = $row['valeur'] ?? $row['indice'] ?? null;

            // Ligne entièrement vide → ignorée silencieusement.
            if ($catRaw === '' && $classeRaw === '' && $echelonRaw === '' && ($valeurRaw === null || $valeurRaw === '')) {
                continue;
            }

            $categorieId = $this->resoudre($catRaw, $this->categories, $this->categoriesParLibelle);
            $classeId = $this->resoudre($classeRaw, $this->classes, $this->classesParLibelle);
            $echelonId = $this->resoudre($echelonRaw, $this->echelons, $this->echelonsParLibelle);

            if (! $categorieId) {
                $this->erreurs[] = "Ligne {$ligne} : catégorie « {$catRaw} » introuvable.";
                continue;
            }
            if (! $classeId) {
                $this->erreurs[] = "Ligne {$ligne} : classe « {$classeRaw} » introuvable.";
                continue;
            }
            if (! $echelonId) {
                $this->erreurs[] = "Ligne {$ligne} : échelon « {$echelonRaw} » introuvable.";
                continue;
            }
            if ($valeurRaw === null || $valeurRaw === '' || ! is_numeric($valeurRaw)) {
                $this->erreurs[] = "Ligne {$ligne} : valeur d'indice manquante ou non numérique.";
                continue;
            }

            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                $code = mb_strtoupper("{$catRaw}-{$classeRaw}-{$echelonRaw}");
            }

            $libelle = trim((string) ($row['libelle'] ?? ''));
            if ($libelle === '') {
                $libelle = "Cat. {$catRaw} · Classe {$classeRaw} · Échelon {$echelonRaw}";
            }

            try {
                $existant = Indice::where('categorie_id', $categorieId)
                    ->where('classe_id', $classeId)
                    ->where('echelon_id', $echelonId)
                    ->first();

                if ($existant) {
                    $existant->update(['valeur' => (int) $valeurRaw, 'libelle' => $libelle]);
                    $this->maj++;
                } else {
                    Indice::create([
                        'code'         => $code,
                        'libelle'      => $libelle,
                        'valeur'       => (int) $valeurRaw,
                        'categorie_id' => $categorieId,
                        'classe_id'    => $classeId,
                        'echelon_id'   => $echelonId,
                        'actif'        => true,
                    ]);
                    $this->importes++;
                }
            } catch (\Throwable $e) {
                $this->erreurs[] = "Ligne {$ligne} : {$e->getMessage()}";
            }
        }
    }

    /** Normalise une map valeur=>id en clés minuscules trimées. */
    private function indexer(Collection $map): Collection
    {
        return $map->mapWithKeys(fn ($id, $cle) => [mb_strtolower(trim((string) $cle)) => $id]);
    }

    /** Résout un identifiant par code puis, en repli, par libellé. */
    private function resoudre(string $valeur, Collection $parCode, Collection $parLibelle): ?int
    {
        if ($valeur === '') {
            return null;
        }
        $cle = mb_strtolower($valeur);
        return $parCode[$cle] ?? $parLibelle[$cle] ?? null;
    }
}
