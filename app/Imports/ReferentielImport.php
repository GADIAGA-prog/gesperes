<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import Excel/CSV générique d'un référentiel, piloté par sa configuration
 * (App\Support\ReferentielRegistry). Le format attendu correspond exactement
 * aux colonnes de l'export : code, libelle, [champs…], actif.
 *
 *  - Upsert par `code` (mise à jour si le code existe déjà).
 *  - Champs « select » (clé étrangère) : résolus par le CODE (ou le libellé) de l'élément lié.
 *  - Champs « enum » : résolus par valeur ou par libellé.
 *  - Booléens : Oui/Non, 1/0, true/false.
 * Les lignes invalides sont collectées dans $erreurs sans interrompre l'import.
 */
class ReferentielImport implements ToCollection, WithHeadingRow
{
    public int $importes = 0;
    public int $maj = 0;
    public array $erreurs = [];

    /** @var array<string,Collection> champ select => [clé minuscule => id] */
    private array $selectMaps = [];

    public function __construct(private string $type, private array $config)
    {
        foreach ($config['champs'] as $nom => $def) {
            if (($def['type'] ?? null) === 'select' && isset($def['source'])) {
                $parCode = $def['source']::pluck('id', 'code')
                    ->mapWithKeys(fn ($id, $code) => [mb_strtolower(trim((string) $code)) => $id]);
                $parLibelle = $def['source']::pluck('id', 'libelle')
                    ->mapWithKeys(fn ($id, $lib) => [mb_strtolower(trim((string) $lib)) => $id]);
                $this->selectMaps[$nom] = $parCode->union($parLibelle);
            }
        }
    }

    public function collection(Collection $rows): void
    {
        $model = $this->config['model'];

        foreach ($rows as $i => $row) {
            $ligne = $i + 2; // +1 en-tête, +1 base 0
            $code = trim((string) ($row['code'] ?? ''));

            if ($code === '') {
                continue; // ligne sans code → ignorée
            }

            try {
                $attrs = [
                    'libelle' => trim((string) ($row['libelle'] ?? '')) ?: $code,
                    'actif'   => $this->bool($row['actif'] ?? true),
                ];

                foreach ($this->config['champs'] as $nom => $def) {
                    $entete = str_ends_with($nom, '_id') ? substr($nom, 0, -3) : $nom;
                    // Accepte « champ », « champ_id » et la convention GESPER « champ_code ».
                    $valeur = $row[$entete] ?? $row[$nom] ?? $row[$entete . '_code'] ?? null;
                    $resolu = $this->resoudre($nom, $def, $valeur, $ligne);
                    if ($resolu !== null || $valeur === null || $valeur === '') {
                        $attrs[$nom] = $resolu;
                    } else {
                        $attrs[$nom] = null; // valeur fournie mais non résolue (erreur déjà notée)
                    }
                }

                $existant = $model::where('code', $code)->first();
                if ($existant) {
                    $existant->update($attrs);
                    $this->maj++;
                } else {
                    $model::create(array_merge(['code' => $code], $attrs));
                    $this->importes++;
                }
            } catch (\Throwable $e) {
                $this->erreurs[] = "Ligne {$ligne} : " . $e->getMessage();
            }
        }
    }

    private function resoudre(string $nom, array $def, $valeur, int $ligne): mixed
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        return match ($def['type']) {
            'select'  => $this->resoudreSelect($nom, (string) $valeur, $ligne),
            'enum'    => $this->resoudreEnum($def['enum'], (string) $valeur, $ligne),
            'boolean' => $this->bool($valeur),
            'number'  => is_numeric($valeur) ? (int) $valeur : null,
            default   => trim((string) $valeur),
        };
    }

    private function resoudreSelect(string $nom, string $valeur, int $ligne): ?int
    {
        $id = $this->selectMaps[$nom][mb_strtolower(trim($valeur))] ?? null;
        if ($id === null) {
            $this->erreurs[] = "Ligne {$ligne} : « {$valeur} » introuvable pour le champ {$nom}.";
        }
        return $id;
    }

    private function resoudreEnum(string $enumClass, string $valeur, int $ligne): ?string
    {
        $valeur = trim($valeur);
        foreach ($enumClass::cases() as $cas) {
            if (mb_strtolower($cas->value) === mb_strtolower($valeur)
                || (method_exists($cas, 'label') && mb_strtolower($cas->label()) === mb_strtolower($valeur))) {
                return $cas->value;
            }
        }
        $this->erreurs[] = "Ligne {$ligne} : valeur « {$valeur} » non reconnue (énumération).";
        return null;
    }

    private function bool($valeur): bool
    {
        $v = mb_strtolower(trim((string) $valeur));
        return in_array($v, ['1', 'oui', 'true', 'vrai', 'yes', 'x', 'actif'], true);
    }
}
