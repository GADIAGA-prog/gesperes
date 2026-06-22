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
        'code', 'libelle', 'type', 'parent_id', 'region', 'province',
        'localite_id', 'responsable_agent_id', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'type' => TypeStructure::class,
    ];

    public function parent() { return $this->belongsTo(Structure::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(Structure::class, 'parent_id'); }
    public function localite() { return $this->belongsTo(Localite::class); }
    public function responsable() { return $this->belongsTo(Agent::class, 'responsable_agent_id'); }
    public function agents() { return $this->hasMany(Agent::class); }

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
