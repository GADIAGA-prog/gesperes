<?php

namespace Database\Seeders;

use App\Models\BaremeAstreinte;
use App\Models\BaremeLogement;
use App\Models\BaremeSpecifique;
use App\Models\BaremeTechnicite;
use App\Models\Emploi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Charge les barèmes d'indemnités à partir des fichiers CORRIGÉS :
 *   - Logement  ← indemnité/Logement_traité.xlsx (catégorie × enseignant × en-classe)
 *   - Astreinte ← indemnité/Astreinte_VF.xlsx     (emploi [intitulé] × zone)
 *   - Spécifique ← GESPER/salaire/Specifique harmonisé.xlsx (emploi_code × zone)
 *   - Technicité ← GESPER/salaire/Technicites.xlsx (échelle)
 *
 * Idempotent : chaque table de barème est vidée puis rechargée. Les intitulés
 * d'astreinte non rattachés à un emploi sont signalés (emplois à identifier).
 */
class BaremesCorrigesSeeder extends Seeder
{
    public function run(): void
    {
        $ind = rtrim((string) config('gesperes.gesper_indemnite_path'), '/\\');
        $sal = rtrim((string) config('gesperes.gesper_salaire_path'), '/\\');

        $this->logement($ind . '/Logement_traité.xlsx');
        $this->astreinte($ind . '/Astreinte_VF.xlsx');
        $this->specifique($sal . '/Specifique harmonisé.xlsx');
        $this->technicite($sal . '/Technicites.xlsx');
    }

    /** Logement : CATEGORIE | Enseignant (Oui/Non) | EnClasse (Oui/Non) | Logement. */
    private function logement(string $fichier): void
    {
        BaremeLogement::query()->delete();
        $n = 0;
        foreach ($this->lire($fichier) as $r) {
            $cat = strtoupper(trim((string) ($r['categorie'] ?? '')));
            if ($cat === '') {
                continue;
            }
            BaremeLogement::create([
                'categorie_code' => $cat,
                'enseignant'     => $this->oui($r['enseignant'] ?? ''),
                'en_classe'      => $this->oui($r['enclasse'] ?? ''),
                'montant'        => (float) ($r['logement'] ?? 0),
                'actif'          => true,
            ]);
            $n++;
        }
        $this->command->info("✓ Logement : {$n} ligne(s) (Logement_traité).");
    }

    /** Astreinte : Intitulé (emploi) | ZONE | Montant. */
    private function astreinte(string $fichier): void
    {
        BaremeAstreinte::query()->delete();
        $emplois = $this->mapEmplois();

        $n = 0;
        $inconnus = [];
        foreach ($this->lire($fichier) as $r) {
            $intitule = trim((string) ($r['intitule'] ?? ''));
            $zone = $this->zone($r['zone'] ?? '');
            if ($intitule === '' || $zone === '') {
                continue;
            }
            $code = $emplois[$this->norm($intitule)] ?? null;
            if (! $code) {
                $inconnus[$intitule] = true;
                continue;
            }
            BaremeAstreinte::updateOrCreate(
                ['emploi_code' => $code, 'zone_code' => $zone],
                ['montant' => (float) ($r['montant'] ?? 0), 'actif' => true]
            );
            $n++;
        }
        $this->command->info("✓ Astreinte : {$n} ligne(s) (Astreinte_VF).");
        if ($inconnus !== []) {
            $this->command->warn('  ⚠ Intitulés non rattachés à un emploi (' . count($inconnus) . ') : '
                . implode(' · ', array_slice(array_keys($inconnus), 0, 12)) . (count($inconnus) > 12 ? '…' : ''));
        }
    }

    /** Spécifique harmonisé : emploi_code | zone_code | montant_specifique_harmonise. */
    private function specifique(string $fichier): void
    {
        BaremeSpecifique::query()->delete();
        $n = 0;
        foreach ($this->lire($fichier) as $r) {
            $emploi = trim((string) $this->col($r, 'emploi'));
            $zone = $this->zone($this->col($r, 'zone'));
            if ($emploi === '' || $zone === '') {
                continue;
            }
            BaremeSpecifique::updateOrCreate(
                ['emploi_code' => $emploi, 'zone_code' => $zone],
                ['montant' => (float) $this->col($r, 'montant'), 'actif' => true]
            );
            $n++;
        }
        $this->command->info("✓ Spécifique : {$n} ligne(s) (Specifique harmonisé).");
    }

    /** Technicité : echelle_code | montant_technicite. */
    private function technicite(string $fichier): void
    {
        BaremeTechnicite::query()->delete();
        $n = 0;
        foreach ($this->lire($fichier) as $r) {
            $echelle = strtoupper(trim((string) $this->col($r, 'echelle')));
            if ($echelle === '') {
                continue;
            }
            BaremeTechnicite::updateOrCreate(
                ['echelle_code' => $echelle],
                ['montant' => (float) $this->col($r, 'montant'), 'actif' => true]
            );
            $n++;
        }
        $this->command->info("✓ Technicité : {$n} ligne(s).");
    }

    // --- Helpers ---

    /** Emplois : code normalisé + libellé normalisé → code (+ alias). */
    private function mapEmplois(): array
    {
        $map = [];
        foreach (Emploi::all() as $e) {
            $map[$this->norm($e->libelle)] = $e->code;
            $map[$this->norm($e->code)] = $e->code;
        }
        foreach ((array) config('emplois_alias', []) as $libelle => $code) {
            $map[$this->norm($libelle)] = $code;
        }
        return $map;
    }

    /** Lit un xlsx → lignes associatives (clés = en-têtes normalisés). */
    private function lire(string $fichier): array
    {
        if (! is_file($fichier)) {
            $this->command->warn("⚠ Fichier absent : {$fichier}");
            return [];
        }

        $rows = IOFactory::load($fichier)->getActiveSheet()->toArray(null, true, false, false);
        $entetes = array_map(fn ($h) => $this->norm($h), array_shift($rows) ?? []);

        $lignes = [];
        foreach ($rows as $r) {
            if (count(array_filter($r, fn ($c) => $c !== null && $c !== '')) === 0) {
                continue;
            }
            $lignes[] = array_combine($entetes, array_pad(array_slice($r, 0, count($entetes)), count($entetes), null));
        }
        return $lignes;
    }

    /** Valeur de la première colonne dont l'en-tête (normalisé) contient le terme. */
    private function col(array $r, string $contient)
    {
        foreach ($r as $cle => $valeur) {
            if (str_contains((string) $cle, $contient)) {
                return $valeur;
            }
        }
        return null;
    }

    private function zone($valeur): string
    {
        $n = $this->norm($valeur);
        return match (true) {
            str_contains($n, 'semi')   => 'semi_urbaine',
            str_contains($n, 'urbain') => 'urbaine',
            str_contains($n, 'rural')  => 'rurale',
            default => '',
        };
    }

    private function oui($valeur): bool
    {
        return str_starts_with($this->norm($valeur), 'oui');
    }

    private function norm($s): string
    {
        $s = Str::of((string) $s)->ascii()->lower()->value();
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $s));
    }
}
