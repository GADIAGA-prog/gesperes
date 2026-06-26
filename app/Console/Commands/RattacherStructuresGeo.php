<?php

namespace App\Console\Commands;

use App\Models\Localite;
use App\Models\Province;
use App\Models\Region;
use App\Models\Structure;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Rattache les structures au référentiel géographique en faisant correspondre
 * leur libellé à une commune, une province ou une région. Une correspondance
 * province renseigne aussi la région (province.region_id) ; une commune renseigne
 * la province et la région. Permet l'auto-remplissage géographique par héritage
 * du parent dans le formulaire de structure.
 *
 * Idempotent.
 */
class RattacherStructuresGeo extends Command
{
    protected $signature = 'structures:geo-rattacher {--dry-run}';

    protected $description = 'Renseigne region_id/province_id/localite_id des structures par correspondance de libellé.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $norm = fn ($s) => (string) Str::of((string) $s)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();

        $regions   = Region::get(['id', 'libelle'])->keyBy(fn ($r) => $norm($r->libelle));
        $provinces = Province::get(['id', 'libelle', 'region_id'])->keyBy(fn ($p) => $norm($p->libelle));
        $localites = Localite::whereNotNull('province_id')->get(['id', 'libelle', 'province_id'])
            ->keyBy(fn ($l) => $norm($l->libelle));
        $provById  = $provinces->keyBy('id');

        $stats = ['commune' => 0, 'province' => 0, 'region' => 0, 'aucun' => 0];

        foreach (Structure::all() as $s) {
            $k = $norm($s->libelle);
            $r = $p = $l = null;

            if ($loc = $localites->get($k)) {
                $l = $loc->id;
                $p = $loc->province_id;
                $r = optional($provById->get($p))->region_id;
                $stats['commune']++;
            } elseif ($prov = $provinces->get($k)) {
                $p = $prov->id;
                $r = $prov->region_id;
                $stats['province']++;
            } elseif ($reg = $regions->get($k)) {
                $r = $reg->id;
                $stats['region']++;
            } else {
                $stats['aucun']++;
                continue;
            }

            $s->region_id = $r;
            $s->province_id = $p;
            $s->localite_id = $l;
            if (! $dry) {
                $s->save();
            }
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Rattachement géographique des structures :');
        $this->table(['Correspondance', 'Structures'], [
            ['Commune', $stats['commune']],
            ['Province (+ région)', $stats['province']],
            ['Région', $stats['region']],
            ['Aucune', $stats['aucun']],
        ]);

        return self::SUCCESS;
    }
}
