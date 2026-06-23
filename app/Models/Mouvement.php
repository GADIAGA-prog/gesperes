<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    use Auditable;

    protected $fillable = [
        'agent_id', 'ancienne_position_id', 'nouvelle_position_id',
        'date_effet', 'date_fin', 'reference_acte', 'motif', 'created_by',
    ];

    protected $casts = [
        'date_effet' => 'date',
        'date_fin' => 'date',
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function anciennePosition() { return $this->belongsTo(PositionAdministrative::class, 'ancienne_position_id'); }
    public function nouvellePosition() { return $this->belongsTo(PositionAdministrative::class, 'nouvelle_position_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }

    /** Famille du mouvement, déduite de la position cible. */
    public function getFamilleAttribute()
    {
        return $this->nouvellePosition?->categorie;
    }
}
