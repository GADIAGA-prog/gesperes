<?php

namespace App\Console\Commands;

use App\Models\Structure;
use App\Models\Zone;
use Illuminate\Console\Command;

/**
 * Nettoie les libellés des directions déconcentrées : retire les préfixes
 * « DRESFPT- » (régions) et « DPESFPT- » (provinces) pour ne garder que le nom
 * géographique, et classe la ZONE de chaque direction (pour astreinte/spécifique) :
 *   - région Kadiogo (Ouaga) / Guiriko (Bobo) → Urbaine
 *   - autres directions régionales              → Semi-urbaine
 *   - directions provinciales                   → Rurale
 *
 * Idempotent : ne traite que les structures encore préfixées.
 */
class RenommerStructuresGeo extends Command
{
    protected $signature = 'structures:geo-renommer {--dry-run : Analyse sans écriture}';

    protected $description = 'Retire les préfixes DRESFPT-/DPESFPT- des structures et classe leur zone.';

    /** Régions urbaines (Ouagadougou / Bobo-Dioulasso). */
    private const REGIONS_URBAINES = ['Kadiogo', 'Guiriko'];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $zones = Zone::pluck('id', 'code'); // code => id
        foreach (['urbaine', 'semi_urbaine', 'rurale'] as $code) {
            if (! isset($zones[$code])) {
                $this->error("Zone « {$code} » absente de la table zones.");
                return self::FAILURE;
            }
        }

        $stats = ['regionales' => 0, 'provinciales' => 0, 'urbaine' => 0, 'semi_urbaine' => 0, 'rurale' => 0];

        foreach (Structure::where('libelle', 'like', 'DRESFPT-%')
            ->orWhere('libelle', 'like', 'DPESFPT-%')->get() as $s) {

            if (str_starts_with($s->libelle, 'DRESFPT-')) {
                $nom = trim(substr($s->libelle, strlen('DRESFPT-')));
                $zoneCode = in_array($nom, self::REGIONS_URBAINES, true) ? 'urbaine' : 'semi_urbaine';
                $s->region = $nom;
                $stats['regionales']++;
            } else {
                $nom = trim(substr($s->libelle, strlen('DPESFPT-')));
                $zoneCode = 'rurale';
                $s->province = $nom;
                $stats['provinciales']++;
            }

            $s->libelle = $nom;
            $s->zone_id = $zones[$zoneCode];
            $stats[$zoneCode]++;

            if (! $dry) {
                $s->save();
            }
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Renommage des structures géographiques :');
        $this->table(['Indicateur', 'Valeur'], [
            ['Directions régionales (DRESFPT)', $stats['regionales']],
            ['Directions provinciales (DPESFPT)', $stats['provinciales']],
            ['→ zone Urbaine', $stats['urbaine']],
            ['→ zone Semi-urbaine', $stats['semi_urbaine']],
            ['→ zone Rurale', $stats['rurale']],
        ]);
        if ($dry) {
            $this->line('→ Relancez sans --dry-run pour appliquer.');
        }

        return self::SUCCESS;
    }
}
