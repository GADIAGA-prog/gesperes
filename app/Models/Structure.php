<?php

namespace App\Models;

use App\Enums\TypeStructure;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Structure extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'code', 'libelle', 'type', 'parent_id', 'action_id', 'region_id', 'province_id',
        'region', 'province', 'localite_id', 'zone_id', 'responsable_agent_id', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'type' => TypeStructure::class,
    ];

    /** Synchronise les libellés texte région/province depuis les clés étrangères. */
    protected static function booted(): void
    {
        static::saving(function (Structure $structure) {
            if ($structure->region_id) {
                $structure->region = Region::find($structure->region_id)?->libelle;
            }
            if ($structure->province_id) {
                $structure->province = Province::find($structure->province_id)?->libelle;
            }
        });
    }

    public function parent() { return $this->belongsTo(Structure::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(Structure::class, 'parent_id'); }
    public function region() { return $this->belongsTo(Region::class); }
    public function province() { return $this->belongsTo(Province::class); }
    public function localite() { return $this->belongsTo(Localite::class); }
    public function zone() { return $this->belongsTo(Zone::class); }
    public function action() { return $this->belongsTo(Action::class); }
    public function responsable() { return $this->belongsTo(Agent::class, 'responsable_agent_id'); }
    public function agents() { return $this->hasMany(Agent::class); }
    public function activites() { return $this->hasMany(Activite::class); }

    /**
     * Configuration de la cascade hiérarchique pour le JS (Alpine) :
     *  - enfants : [parentKey => [{id, libelle, feuille}]]  (parentKey = 'racine' pour les racines)
     *  - parents : [id => parent_id]  (pour reconstituer le chemin en édition)
     */
    public static function cascadeConfig(): array
    {
        $arbre = static::orderBy('libelle')->get(['id', 'libelle', 'parent_id']);
        $parentsAvecEnfants = $arbre->pluck('parent_id')->filter()->unique()->flip();

        return [
            'enfants' => $arbre
                ->groupBy(fn ($s) => $s->parent_id ? (string) $s->parent_id : 'racine')
                ->map(fn ($grp) => $grp->map(fn ($s) => [
                    'id' => $s->id,
                    'libelle' => $s->libelle,
                    'feuille' => ! $parentsAvecEnfants->has($s->id),
                ])->values()),
            'parents' => $arbre->mapWithKeys(fn ($s) => [$s->id => $s->parent_id]),
        ];
    }

    /** Libellés du chemin hiérarchique, de la racine jusqu'à cette structure. */
    public function cheminNiveaux(): array
    {
        $segments = [];
        $node = $this;
        $guard = 0;
        while ($node && $guard < 12) {
            array_unshift($segments, $node->libelle);
            $node = $node->parent;
            $guard++;
        }
        return $segments;
    }

    public function cheminComplet(): string
    {
        return implode(' › ', $this->cheminNiveaux());
    }

    /**
     * Structure (unité) = avant-dernier niveau de la cascade.
     * S'il n'y a pas de service (un seul niveau), le dernier niveau est la structure.
     */
    public function niveauStructure(): ?string
    {
        $n = $this->cheminNiveaux();
        $c = count($n);
        return $c >= 2 ? $n[$c - 2] : ($n[0] ?? null);
    }

    /** Service = dernier niveau de la cascade (null s'il n'y a qu'un seul niveau). */
    public function niveauService(): ?string
    {
        $n = $this->cheminNiveaux();
        return count($n) >= 2 ? end($n) : null;
    }

    /** Carte id => [libellés des niveaux] pour toutes les structures (une seule requête). */
    public static function cheminsParId(): array
    {
        $all = static::get(['id', 'libelle', 'parent_id'])->keyBy('id');
        $chemins = [];
        foreach ($all as $id => $s) {
            $niveaux = [];
            $node = $s;
            $guard = 0;
            while ($node && $guard < 12) {
                array_unshift($niveaux, $node->libelle);
                $node = $node->parent_id ? ($all[$node->parent_id] ?? null) : null;
                $guard++;
            }
            $chemins[$id] = $niveaux;
        }
        return $chemins;
    }

    /**
     * Carte structure_id => code de zone, résolue par cascade : la zone de la
     * structure, sinon celle de l'ancêtre le plus proche qui en porte une.
     * Permet de dériver la zone d'un agent depuis sa direction régionale/
     * provinciale en une seule requête (mémoïsable côté appelant).
     *
     * @return array<int, string>
     */
    public static function zonesParStructure(): array
    {
        $all = static::get(['id', 'parent_id', 'zone_id'])->keyBy('id');
        $zones = Zone::pluck('code', 'id');

        $resoudre = function ($id) use (&$resoudre, $all, $zones) {
            $node = $all[$id] ?? null;
            $guard = 0;
            while ($node && $guard < 12) {
                if ($node->zone_id && isset($zones[$node->zone_id])) {
                    return $zones[$node->zone_id];
                }
                $node = $node->parent_id ? ($all[$node->parent_id] ?? null) : null;
                $guard++;
            }
            return null;
        };

        $map = [];
        foreach ($all as $id => $_) {
            if ($zone = $resoudre($id)) {
                $map[$id] = $zone;
            }
        }
        return $map;
    }

    /** Profondeur maximale de la hiérarchie (nombre de niveaux). */
    public static function profondeurMax(): int
    {
        $longueurs = array_map('count', static::cheminsParId());
        return $longueurs ? max($longueurs) : 1;
    }

    /**
     * Ids d'une structure et de TOUTES ses descendantes (sous-arbre complet).
     * Permet de filtrer « DRH » et d'inclure les agents rattachés à ses services
     * (ex. service de gestion des carrières), pas seulement la DRH elle-même.
     */
    public static function sousArbreIds(int|string|null $racineId): array
    {
        $racineId = (int) $racineId;
        if (! $racineId) {
            return [];
        }

        // Carte parent_id => [ids enfants], en une seule requête.
        $enfantsParParent = [];
        foreach (static::pluck('parent_id', 'id') as $id => $parentId) {
            $enfantsParParent[(int) $parentId][] = (int) $id;
        }

        $ids = [];
        $pile = [$racineId];
        while ($pile) {
            $courant = array_pop($pile);
            if (isset($ids[$courant])) {
                continue;
            }
            $ids[$courant] = true;
            foreach ($enfantsParParent[$courant] ?? [] as $enfant) {
                $pile[] = $enfant;
            }
        }

        return array_keys($ids);
    }

    /**
     * Ids des structures dont le libellé contient le terme, ET de tout leur
     * sous-arbre. Permet une recherche par structure « cascade » : saisir le
     * libellé d'une direction ramène aussi les agents de ses services.
     * Une seule requête, traversée en mémoire.
     */
    public static function idsParLibelleEtSousArbre(string $terme): array
    {
        $terme = trim($terme);
        if ($terme === '') {
            return [];
        }

        $toutes = static::get(['id', 'parent_id', 'libelle']);

        $racines = $toutes
            ->filter(fn ($s) => mb_stripos((string) $s->libelle, $terme) !== false)
            ->pluck('id');

        if ($racines->isEmpty()) {
            return [];
        }

        $enfantsParParent = [];
        foreach ($toutes as $s) {
            $enfantsParParent[(int) $s->parent_id][] = (int) $s->id;
        }

        $ids = [];
        $pile = $racines->map(fn ($id) => (int) $id)->all();
        while ($pile) {
            $courant = array_pop($pile);
            if (isset($ids[$courant])) {
                continue;
            }
            $ids[$courant] = true;
            foreach ($enfantsParParent[$courant] ?? [] as $enfant) {
                $pile[] = $enfant;
            }
        }

        return array_keys($ids);
    }
}
