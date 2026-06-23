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

    public function cheminComplet(): string
    {
        $segments = [];
        $node = $this;
        $guard = 0;
        while ($node && $guard < 12) {
            array_unshift($segments, $node->libelle);
            $node = $node->parent;
            $guard++;
        }
        return implode(' › ', $segments);
    }
}
