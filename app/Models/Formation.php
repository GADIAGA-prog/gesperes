<?php

namespace App\Models;

use App\Enums\StatutFormation;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formation extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'action_formation_id', 'intitule', 'organisme', 'lieu', 'type', 'date_debut', 'date_fin',
        'cout', 'statut', 'description', 'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'cout' => 'decimal:2',
        'statut' => StatutFormation::class,
    ];

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'formation_agent')
            ->withPivot(['resultat', 'observation'])->withTimestamps();
    }

    public function createur() { return $this->belongsTo(User::class, 'created_by'); }

    /** Action du plan de formation dont cette session découle (le cas échéant). */
    public function actionFormation() { return $this->belongsTo(ActionFormation::class, 'action_formation_id'); }
}
