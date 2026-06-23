<?php

namespace App\Models;

use App\Enums\TypeEvenementCarriere;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class CarriereEvenement extends Model
{
    use Auditable;

    protected $table = 'carriere_evenements';

    protected $fillable = [
        'agent_id', 'type', 'date_effet',
        'ancienne_categorie_id', 'nouvelle_categorie_id',
        'ancienne_echelle_id', 'nouvelle_echelle_id',
        'ancienne_classe_id', 'nouvelle_classe_id',
        'ancien_echelon_id', 'nouvel_echelon_id',
        'ancien_indice_id', 'nouvel_indice_id',
        'ancienne_fonction_id', 'nouvelle_fonction_id',
        'ancien_poste_id', 'nouveau_poste_id',
        'ancienne_position_id', 'nouvelle_position_id',
        'reference_acte', 'description', 'observation', 'created_by',
    ];

    protected $casts = [
        'date_effet' => 'date',
        'type' => TypeEvenementCarriere::class,
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
