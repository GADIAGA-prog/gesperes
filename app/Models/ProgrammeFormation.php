<?php

namespace App\Models;

use App\Enums\StatutPlanFormation;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Déclinaison annuelle d'un plan de formation (ex. « Programme 2019 »).
 * Regroupe les actions de formation de l'année et porte le budget prévisionnel.
 */
class ProgrammeFormation extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'programmes_formation';

    protected $fillable = [
        'plan_formation_id', 'annee', 'objectif_strategique',
        'budget_previsionnel', 'statut',
    ];

    protected $casts = [
        'annee'               => 'integer',
        'budget_previsionnel' => 'decimal:2',
        'statut'              => StatutPlanFormation::class,
    ];

    public function plan() { return $this->belongsTo(PlanFormation::class, 'plan_formation_id'); }
    public function actions() { return $this->hasMany(ActionFormation::class)->orderBy('numero_ordre'); }
}
