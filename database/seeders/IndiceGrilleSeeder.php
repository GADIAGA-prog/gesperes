<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Indice;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Réimporte intégralement la grille indiciaire officielle depuis indices.xlsx
 * (classification du fichier « Bon_annexe nouveau classement indiciaire »).
 *
 * Le code échelon encode la position dans la grille :
 *   A/B/C/D/E :  Cat(1) + Échelle(1) + Classe(1) + Échelon(1-2)   ex. A111, A1110
 *   P         :  P + Échelle(A/B/C)  + Classe(1) + Échelon(1-2)    ex. PA11, PA110
 *
 * Idempotent : chaque indice est mis à jour (clé = code) sans casser les
 * agents qui le référencent.
 */
class IndiceGrilleSeeder extends Seeder
{
    public function run(): void
    {
        $fichier = (string) config('gesperes.gesper_indices_path');

        if (! is_file($fichier)) {
            $this->command->warn("⚠ Fichier indices introuvable : {$fichier} — grille non réimportée.");
            return;
        }

        // Maps code → id des référentiels de classement.
        $categories = Categorie::pluck('id', 'code');   // A, B, C, D, E, P
        $echelles   = Echelle::pluck('id', 'code');     // ECHL1..3, ECHLA/B/C
        $classes    = Classe::pluck('id', 'code');      // CL1..3
        $echelons   = Echelon::pluck('id', 'code');     // ECH1..ECH19

        $rows = IOFactory::load($fichier)->getActiveSheet()->toArray(null, true, false, false);
        array_shift($rows); // en-tête echelon_code | indice

        $crees = 0;
        $maj = 0;
        $ignores = [];

        foreach ($rows as $r) {
            $code = strtoupper(trim((string) ($r[0] ?? '')));
            $valeur = (int) ($r[1] ?? 0);
            if ($code === '' || $valeur === 0) {
                continue;
            }

            $parts = $this->decomposer($code);
            if ($parts === null) {
                $ignores[] = $code;
                continue;
            }
            [$cat, $echelle, $classe, $echelon] = $parts;

            $catId     = $categories[$cat] ?? null;
            $echelleId = $echelles['ECHL' . $echelle] ?? null;
            $classeId  = $classes['CL' . $classe] ?? null;
            $echelonId = $echelons['ECH' . $echelon] ?? null;

            if (! $catId || ! $echelleId || ! $classeId || ! $echelonId) {
                $ignores[] = $code;
                continue;
            }

            $indice = Indice::firstOrNew(['code' => $code]);
            $existait = $indice->exists;

            $indice->fill([
                'valeur'       => $valeur,
                'libelle'      => "Catégorie {$cat} · Échelle {$echelle} · Classe {$classe} · Échelon {$echelon}",
                'categorie_id' => $catId,
                'echelle_id'   => $echelleId,
                'classe_id'    => $classeId,
                'echelon_id'   => $echelonId,
                'actif'        => true,
            ])->save();

            $existait ? $maj++ : $crees++;
        }

        $this->command->info("✓ Grille indiciaire : {$crees} créé(s), {$maj} mis à jour.");
        if ($ignores !== []) {
            $this->command->warn('⚠ ' . count($ignores) . ' code(s) non rattaché(s) : ' . implode(', ', array_slice($ignores, 0, 15)) . (count($ignores) > 15 ? '…' : ''));
        }
    }

    /** Décompose un code échelon en [catégorie, échelle, classe, échelon]. */
    private function decomposer(string $code): ?array
    {
        if (strlen($code) < 4) {
            return null;
        }

        if ($code[0] === 'P') {
            // P + lettre d'échelle (A/B/C) + classe (1 chiffre) + échelon (reste)
            return ['P', $code[1], $code[2], substr($code, 3)];
        }

        // Cat (1 lettre) + échelle (1 chiffre) + classe (1 chiffre) + échelon (reste)
        return [$code[0], $code[1], $code[2], substr($code, 3)];
    }
}
