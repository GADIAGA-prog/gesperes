<?php

namespace Database\Seeders;

use App\Models\BaremeAstreinte;
use App\Models\BaremeLogement;
use App\Models\BaremeSpecifique;
use App\Models\BaremeTechnicite;
use App\Models\Indemnite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Charge les barèmes d'indemnités (décret 2014-427) depuis les fichiers GESPER
 * (dossier config gesperes.gesper_salaire_path) et déclare les 4 indemnités au
 * référentiel. Idempotent : remplace le contenu des barèmes à chaque exécution.
 */
class IndemniteBaremeSeeder extends Seeder
{
    public function run(): void
    {
        $dir = rtrim((string) config('gesperes.gesper_salaire_path'), '/\\');

        if (! is_dir($dir)) {
            $this->command->warn("⚠ Dossier GESPER introuvable : {$dir} — barèmes non importés.");
            return;
        }

        // Indemnités du catalogue (mode barème).
        $catalogue = [
            ['ASTR', 'Astreinte', 'astreinte'],
            ['SPEC', 'Indemnité spécifique harmonisée', 'specifique'],
            ['LOG',  'Indemnité de logement', 'logement'],
            ['TECH', 'Indemnité de technicité', 'technicite'],
        ];
        foreach ($catalogue as [$code, $libelle, $bareme]) {
            Indemnite::updateOrCreate(['code' => $code], [
                'libelle' => $libelle, 'mode' => 'bareme', 'bareme' => $bareme,
                'valeur' => 0, 'reference_texte' => 'décret 2014-427', 'actif' => true,
            ]);
        }

        $n = 0;
        $n += $this->importerEmploiZone($dir . '/astreinte.xlsx', BaremeAstreinte::class, 'montant_astreinte');
        $n += $this->importerEmploiZone($dir . '/Specifique harmonisé.xlsx', BaremeSpecifique::class, 'montant_specifique_harmonise');
        $n += $this->importerLogement($dir . '/LOGEMENT_vf.xlsx');
        $n += $this->importerTechnicite($dir . '/Technicites.xlsx');

        $this->command->info("✓ Barèmes d'indemnités GESPER importés ({$n} lignes).");
    }

    /** Barèmes emploi × zone (astreinte, spécifique). */
    private function importerEmploiZone(string $fichier, string $modele, string $colMontant): int
    {
        $modele::query()->delete();
        $count = 0;
        foreach ($this->lire($fichier) as $r) {
            $emploi = trim((string) ($r['emploi_code'] ?? ''));
            $zone = $this->zone($r['zone_code'] ?? '');
            if ($emploi === '' || $zone === '') {
                continue;
            }
            $modele::updateOrCreate(
                ['emploi_code' => $emploi, 'zone_code' => $zone],
                ['montant' => (float) ($r[$colMontant] ?? 0), 'actif' => $this->bool($r['actif'] ?? true)]
            );
            $count++;
        }
        return $count;
    }

    private function importerLogement(string $fichier): int
    {
        BaremeLogement::query()->delete();
        $count = 0;
        foreach ($this->lire($fichier) as $r) {
            $cat = trim((string) ($r['categorie_code'] ?? ''));
            if ($cat === '') {
                continue;
            }
            BaremeLogement::updateOrCreate(
                [
                    'categorie_code' => $cat,
                    'enseignant'     => $this->bool($r['enseignant'] ?? false),
                    'en_classe'      => $this->bool($r['en_classe'] ?? false),
                ],
                ['montant' => (float) ($r['montant_logement'] ?? 0), 'actif' => $this->bool($r['actif'] ?? true)]
            );
            $count++;
        }
        return $count;
    }

    private function importerTechnicite(string $fichier): int
    {
        BaremeTechnicite::query()->delete();
        $count = 0;
        foreach ($this->lire($fichier) as $r) {
            $echelle = trim((string) ($r['echelle_code'] ?? ''));
            if ($echelle === '') {
                continue;
            }
            BaremeTechnicite::updateOrCreate(
                ['echelle_code' => $echelle],
                ['montant' => (float) ($r['montant_technicite'] ?? 0), 'actif' => $this->bool($r['actif'] ?? true)]
            );
            $count++;
        }
        return $count;
    }

    /** Lit un xlsx et renvoie des lignes associatives (clés = en-têtes normalisés). */
    private function lire(string $fichier): array
    {
        if (! is_file($fichier)) {
            $this->command->warn("⚠ Fichier absent : {$fichier}");
            return [];
        }

        $rows = IOFactory::load($fichier)->getActiveSheet()->toArray(null, true, false, false);
        $entetes = array_map(fn ($h) => Str::of((string) $h)->ascii()->lower()->trim()
            ->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value(), array_shift($rows) ?? []);

        $lignes = [];
        foreach ($rows as $r) {
            if (count(array_filter($r, fn ($c) => $c !== null && $c !== '')) === 0) {
                continue;
            }
            $lignes[] = array_combine($entetes, array_pad(array_slice($r, 0, count($entetes)), count($entetes), null));
        }
        return $lignes;
    }

    private function zone($valeur): string
    {
        return Str::of((string) $valeur)->ascii()->lower()->trim()
            ->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value();
    }

    private function bool($valeur): bool
    {
        return in_array(mb_strtolower(trim((string) $valeur)), ['1', 'oui', 'true', 'vrai', 'yes', 'x'], true);
    }
}
