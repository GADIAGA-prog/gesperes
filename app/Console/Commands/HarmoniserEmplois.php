<?php

namespace App\Console\Commands;

use App\Models\Emploi;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Harmonise les emplois du fichier nominatif « SITUATION DU PERSONNEL » avec le
 * référentiel : les libellés déjà connus (directement ou via config/emplois_alias)
 * sont ignorés ; les libellés réellement absents sont CRÉÉS dans le référentiel.
 *
 * Lecture seule du fichier, idempotente côté référentiel. À lancer avant l'import
 * des agents (emplois:harmoniser).
 */
class HarmoniserEmplois extends Command
{
    protected $signature = 'emplois:harmoniser {--dry-run : Affiche sans rien créer}';

    protected $description = 'Crée dans le référentiel les emplois du fichier SITUATION absents (hors alias).';

    /** Libellés enseignants déduits du libellé (pour le lieu d\'exercice, R8/R10). */
    private const ENSEIGNANT_MOTS = ['professeur', 'enseignant', 'formateur', 'instituteur'];

    public function handle(): int
    {
        $fichier = (string) config('gesperes.gesper_situation_path');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        // Référentiel + alias (clés normalisées).
        $connus = [];
        foreach (Emploi::all() as $e) {
            $connus[$this->norm($e->libelle)] = true;
            $connus[$this->norm($e->code)] = true;
        }
        $codes = Emploi::pluck('code')->map(fn ($c) => strtoupper($c))->all();
        $alias = [];
        foreach ((array) config('emplois_alias', []) as $libelle => $code) {
            $alias[$this->norm($libelle)] = $code;
        }

        $emplois = $this->emploisDuFichier($fichier);
        $this->info(count($emplois) . ' libellé(s) EMPLOI distinct(s) dans le fichier.');

        $crees = 0;
        $ignoresAlias = 0;
        foreach ($emplois as $valeur => $nb) {
            $n = $this->norm($valeur);

            if ($n === '' || $n === 'emploi') {
                continue; // ligne d'en-tête recopiée
            }
            if (isset($connus[$n])) {
                continue; // déjà au référentiel
            }
            if (isset($alias[$n])) {
                $ignoresAlias++;
                continue; // rattaché à un emploi existant lors de l'import
            }

            $code = $this->genererCode($valeur, $codes);
            $enseignant = Str::contains($this->norm($valeur), self::ENSEIGNANT_MOTS);

            $this->line(sprintf('  + %-8s %s%s', $code, $valeur, $enseignant ? '  [enseignant]' : ''));

            if (! $dry) {
                Emploi::create([
                    'code' => $code,
                    'libelle' => $valeur,
                    'enseignant' => $enseignant,
                    'actif' => true,
                ]);
            }
            $codes[] = $code;
            $connus[$n] = true;
            $crees++;
        }

        $this->newLine();
        $this->info(($dry ? '[dry-run] ' : '') . "✓ {$crees} emploi(s) créé(s), {$ignoresAlias} rattaché(s) par alias.");

        return self::SUCCESS;
    }

    /** Libellés EMPLOI distincts (col. G) du fichier, avec leur effectif. */
    private function emploisDuFichier(string $fichier): array
    {
        $reader = IOFactory::createReaderForFile($fichier);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['BASE GLOBALE']);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell($col, $row, $ws = ''): bool
            {
                return Coordinate::columnIndexFromString($col) <= 14 && $row >= 10;
            }
        });

        $rows = $reader->load($fichier)->getActiveSheet()->toArray(null, true, false, false);

        $emplois = [];
        foreach ($rows as $i => $r) {
            $matricule = trim((string) ($r[1] ?? ''));
            $emploi = trim((string) ($r[6] ?? ''));
            if ($i === 0 || $matricule === '' || $emploi === '') {
                continue;
            }
            $emplois[$emploi] = ($emplois[$emploi] ?? 0) + 1;
        }
        arsort($emplois);

        return $emplois;
    }

    /** Code unique dérivé du libellé (acronyme des mots significatifs). */
    private function genererCode(string $libelle, array $existants): string
    {
        $stop = ['de', 'des', 'du', 'd', 'en', 'et', 'le', 'la', 'les', 'l', 'ou', 'a', 'au', 'aux'];
        $mots = array_values(array_filter(
            preg_split('/[^a-z0-9]+/', $this->norm($libelle), -1, PREG_SPLIT_NO_EMPTY),
            fn ($m) => ! in_array($m, $stop, true)
        ));

        // Un seul mot significatif → 4 premières lettres ; sinon acronyme des initiales.
        if (count($mots) === 1) {
            $sigle = strtoupper(substr($mots[0], 0, 4));
        } else {
            $sigle = implode('', array_map(fn ($m) => strtoupper($m[0]), $mots));
        }
        $sigle = substr($sigle ?: strtoupper(substr($this->norm($libelle), 0, 4)), 0, 10);

        $code = $sigle;
        $i = 1;
        while (in_array(strtoupper($code), array_map('strtoupper', $existants), true)) {
            $code = $sigle . $i++;
        }

        return $code;
    }

    private function norm(string $s): string
    {
        $s = Str::of($s)->ascii()->lower()->value();

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $s));
    }
}
