<?php

namespace Database\Seeders;

use App\Models\Fonction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Intègre les fonctions et leur indemnité de responsabilité du décret 2014-427
 * (référentiel database/data/indemnites_responsabilite.csv extrait du décret).
 *
 * CRÉATION SEULE : les fonctions déjà présentes (même libellé normalisé) ne sont
 * PAS écrasées. Le décret contient en effet plusieurs tables de portées
 * différentes (général, MEF, hautes institutions) où un même intitulé porte des
 * montants distincts (« Chef de service » = 10 500 / 15 000 / 60 000) : un écrasement
 * automatique corromprait les valeurs déjà validées. Les fonctions du décret sont
 * ainsi ajoutées au référentiel, et le budget les utilise dès qu'un agent y est rattaché.
 *
 * Idempotent.
 */
class DecretFonctionIndemniteSeeder extends Seeder
{
    public function run(): void
    {
        $fichier = database_path('data/indemnites_responsabilite.csv');
        if (! is_file($fichier)) {
            $this->command?->error("Référentiel introuvable : {$fichier}");
            return;
        }

        // Index des fonctions existantes par libellé normalisé.
        $existantes = Fonction::all()->keyBy(fn ($f) => $this->normaliser($f->libelle));
        $codesPris = Fonction::pluck('code')->map(fn ($c) => mb_strtoupper($c))->flip();

        $crees = 0;
        $ignores = 0;

        foreach ($this->lignes($fichier) as [$libelle, $montant]) {
            $cle = $this->normaliser($libelle);

            // Création seule : on n'écrase jamais une fonction déjà présente.
            if ($existantes->has($cle)) {
                $ignores++;
                continue;
            }

            $code = $this->codeUnique($libelle, $codesPris);
            $f = Fonction::create([
                'code' => $code,
                'libelle' => $libelle,
                'indemnite_responsabilite' => $montant,
                'actif' => true,
            ]);
            $existantes->put($cle, $f);
            $crees++;
        }

        $this->command?->info("✓ Décret 2014-427 : {$crees} fonction(s) créée(s), {$ignores} déjà présente(s) (non modifiées).");
    }

    /** @return array<int, array{0:string,1:int}> */
    private function lignes(string $fichier): array
    {
        $rows = array_map('str_getcsv', file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        array_shift($rows); // en-tête

        $out = [];
        foreach ($rows as $r) {
            $libelle = trim((string) ($r[0] ?? ''));
            $montant = (int) preg_replace('/\D/', '', (string) ($r[1] ?? ''));
            if ($libelle !== '' && $montant > 0) {
                $out[] = [$libelle, $montant];
            }
        }
        return $out;
    }

    private function normaliser(string $libelle): string
    {
        return (string) Str::of($libelle)->ascii()->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')->squish();
    }

    private function codeUnique(string $libelle, $codesPris): string
    {
        $base = (string) Str::of($libelle)->ascii()->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->limit(40, '');
        $code = $base !== '' ? $base : 'FONCTION';

        $i = 1;
        while ($codesPris->has($code)) {
            $code = Str::limit($base, 36, '') . '_' . (++$i);
        }
        $codesPris->put($code, true);

        return $code;
    }
}
