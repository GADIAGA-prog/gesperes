<?php

namespace App\Console\Commands;

use App\Enums\TypeStructure;
use App\Models\Agent;
use App\Models\Region;
use App\Models\Structure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reconstruit l'organigramme sur l'ancien découpage des 13 régions + le référentiel
 * géographique : Secrétariat général → Région → Province → Commune/Établissement.
 *
 *  - 13 nœuds Région sous le Secrétariat général (table regions) ;
 *  - chaque structure de province (province_id, sans localité) sous sa région
 *    (province.region_id) — corrige les provinces orphelines ;
 *  - chaque commune/établissement (localite_id) sous sa province ;
 *  - les anciennes directions régionales « nouveau découpage » (17) sont vidées
 *    de leurs agents (réaffectés à l'ancienne région correspondante) puis désactivées.
 *
 * --dry-run pour simuler. Idempotent.
 */
class ReconstruireOrganigramme extends Command
{
    protected $signature = 'structures:reconstruire-organigramme {--dry-run}';

    protected $description = 'Reconstruit la cascade SG → Région (13) → Province → Commune d\'après le référentiel.';

    /** Nouvelle région (structure existante) → ancienne région (table regions). */
    private const NOUVELLE_VERS_ANCIENNE = [
        'Bankui' => 'Boucle du Mouhoun', 'Djôrô' => 'Sud-Ouest', 'Goulmou' => 'Est',
        'Guiriko' => 'Guiriko', 'Kadiogo' => 'Kadiogo', 'Koulsé' => 'Centre-Nord',
        'Liptako' => 'Sahel', 'Nakambé' => 'Centre-Est', 'Nando' => 'Centre-Ouest',
        'Nazinon' => 'Centre-Sud', 'Oubri' => 'Plateau-Central', 'Sirba' => 'Sahel',
        'Soum' => 'Sahel', 'Sourou' => 'Boucle du Mouhoun', 'Tannounya' => 'Cascades',
        'Tapoa' => 'Est', 'Yaada' => 'Nord',
    ];

    private bool $dry;
    private array $stats = ['regions' => 0, 'provinces' => 0, 'communes' => 0, 'agents_rehomes' => 0, 'noeuds_desactives' => 0];

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');

        $sg = Structure::where('libelle', 'Secrétariat général')->whereNull('parent_id')->first()
            ?? Structure::where('libelle', 'Secrétariat général')->first();
        if (! $sg) {
            $this->error('Secrétariat général introuvable.');
            return self::FAILURE;
        }

        $travail = function () use ($sg) {
            $regionNodes = $this->assurerRegions($sg);          // region_id => node
            $this->reaffecterNouvellesRegions($regionNodes);    // vide + désactive les 17
            $provinceNodes = $this->rattacherProvinces($regionNodes); // province_id => node
            $this->rattacherCommunes($regionNodes, $provinceNodes);
        };

        // En réel : transaction atomique. En simulation : sauver() n'écrit rien.
        $this->dry ? $travail() : DB::transaction($travail);

        $this->afficherBilan();
        return self::SUCCESS;
    }

    /** Garantit un nœud Région par région (table regions), sous le SG. */
    private function assurerRegions(Structure $sg): array
    {
        $nodes = [];
        foreach (Region::orderBy('libelle')->get() as $region) {
            $node = Structure::where('libelle', $region->libelle)
                ->whereNull('localite_id')->whereNull('province_id')->first();
            if (! $node) {
                $node = new Structure([
                    'code' => 'REG-' . $region->id,
                    'libelle' => $region->libelle,
                    'type' => TypeStructure::DIRECTION->value,
                ]);
                $this->stats['regions']++;
            }
            $node->parent_id = $sg->id;
            $node->region_id = $region->id;
            $node->actif = true;
            $this->sauver($node);
            $nodes[$region->id] = $node;
        }
        return $nodes;
    }

    /** Vide les 17 directions « nouveau découpage » de leurs agents puis les désactive. */
    private function reaffecterNouvellesRegions(array $regionNodes): void
    {
        $regionsParNom = Region::pluck('id', 'libelle');
        foreach (self::NOUVELLE_VERS_ANCIENNE as $nouvelle => $ancienne) {
            // On ne touche pas Guiriko/Kadiogo : ce SONT déjà des nœuds région.
            if ($nouvelle === $ancienne) {
                continue;
            }
            $node = Structure::where('libelle', $nouvelle)->whereNull('localite_id')->whereNull('province_id')->first();
            if (! $node) {
                continue;
            }
            $cible = $regionNodes[$regionsParNom[$ancienne] ?? null] ?? null;
            if (! $cible) {
                continue;
            }

            $n = Agent::where('structure_id', $node->id)->count();
            if ($n > 0) {
                $this->stats['agents_rehomes'] += $n;
                // Préserve le lien d'action budgétaire : la région hérite de l'action
                // de la direction d'origine si elle n'en a pas encore.
                if (! $cible->action_id && $node->action_id) {
                    $cible->action_id = $node->action_id;
                    $this->sauver($cible);
                }
                if (! $this->dry) {
                    Agent::where('structure_id', $node->id)->update(['structure_id' => $cible->id]);
                }
            }
            $node->actif = false;
            $node->parent_id = $cible->id;
            $this->sauver($node);
            $this->stats['noeuds_desactives']++;
        }
    }

    /** Provinces (province_id, sans localité) → sous leur région. */
    private function rattacherProvinces(array $regionNodes): array
    {
        $nodes = [];
        $provinces = Structure::whereNotNull('province_id')->whereNull('localite_id')
            ->where('actif', true)->orderBy('id')->get();

        foreach ($provinces as $p) {
            $region = $regionNodes[$p->region_id] ?? null;
            if ($region && $p->parent_id !== $region->id) {
                $p->parent_id = $region->id;
                $this->sauver($p);
                $this->stats['provinces']++;
            }
            // 1 nœud province par province_id (le premier rencontré fait foi).
            $nodes[$p->province_id] ??= $p;
        }
        return $nodes;
    }

    /** Communes/établissements (localite_id) → sous leur province. */
    private function rattacherCommunes(array $regionNodes, array $provinceNodes): void
    {
        Structure::whereNotNull('localite_id')->where('actif', true)->orderBy('id')
            ->chunkById(500, function ($lot) use ($regionNodes, $provinceNodes) {
                foreach ($lot as $c) {
                    $cible = $provinceNodes[$c->province_id] ?? ($regionNodes[$c->region_id] ?? null);
                    if ($cible && $c->id !== $cible->id && $c->parent_id !== $cible->id) {
                        $c->parent_id = $cible->id;
                        $this->sauver($c);
                        $this->stats['communes']++;
                    }
                }
            });
    }

    private function sauver(Structure $s): void
    {
        if (! $this->dry) {
            $s->save();
        }
    }

    private function afficherBilan(): void
    {
        $this->info(($this->dry ? '[DRY-RUN] ' : '') . 'Reconstruction de l\'organigramme :');
        $this->table(['Action', 'Nombre'], [
            ['Nœuds Région créés', $this->stats['regions']],
            ['Provinces rattachées à leur région', $this->stats['provinces']],
            ['Communes/établissements rattachés à leur province', $this->stats['communes']],
            ['Agents réaffectés (ex-directions 17)', $this->stats['agents_rehomes']],
            ['Nœuds « nouveau découpage » désactivés', $this->stats['noeuds_desactives']],
        ]);
    }
}
