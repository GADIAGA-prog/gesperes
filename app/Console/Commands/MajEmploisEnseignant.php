<?php

namespace App\Console\Commands;

use App\Models\Emploi;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Met à jour le flag enseignant des emplois depuis le fichier de référence
 * emplois.xlsx (code | libelle | enseignant Oui/Non). Pilote R8/R10.
 */
class MajEmploisEnseignant extends Command
{
    protected $signature = 'emplois:maj-enseignant {--dry-run}';

    protected $description = 'Met à jour emploi.enseignant depuis emplois.xlsx (code | libelle | enseignant).';

    public function handle(): int
    {
        $fichier = base_path('../pour gesperes/emplois.xlsx');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');

        $emplois = Emploi::all()->keyBy(fn ($e) => strtoupper(trim($e->code)));

        $rows = IOFactory::load($fichier)->getActiveSheet()->toArray(null, true, false, false);
        array_shift($rows); // en-tête

        $changes = 0;
        $absents = 0;
        foreach ($rows as $r) {
            $code = strtoupper(trim((string) ($r[0] ?? '')));
            if ($code === '') {
                continue;
            }
            $ens = str_starts_with(mb_strtolower(trim((string) ($r[2] ?? ''))), 'oui');

            $e = $emplois->get($code);
            if (! $e) {
                $absents++;
                continue;
            }
            if ((bool) $e->enseignant !== $ens) {
                $changes++;
                if (! $dry) {
                    $e->enseignant = $ens;
                    $e->saveQuietly();
                }
            }
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ {$changes} emploi(s) corrigé(s) ; {$absents} code(s) du fichier absent(s) du référentiel.");
        $this->line('Enseignants au référentiel : ' . Emploi::where('enseignant', true)->count());

        return self::SUCCESS;
    }
}
