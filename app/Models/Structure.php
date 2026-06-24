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
        'region', 'province', 'localite_id', 'responsable_agent_id', 'actif',
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

    /** Profondeur maximale de la hiérarchie (nombre de niveaux). */
    public static function profondeurMax(): int
    {
        $longueurs = array_map('count', static::cheminsParId());
        return $longueurs ? max($longueurs) : 1;
    }
}
