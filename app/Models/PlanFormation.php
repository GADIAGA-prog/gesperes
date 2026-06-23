<?php

namespace App\Models;

use App\Enums\StatutPlanFormation;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Plan pluriannuel de formation (ex. « Plan triennal 2019-2021 »), décliné en
 * programmes annuels. Porte la vision, la finalité et les objectifs.
 */
class PlanFormation extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'plans_formation';

    protected $fillable = [
        'intitule', 'annee_debut', 'annee_fin', 'vision', 'finalite',
        'objectifs', 'statut', 'created_by',
    ];

    protected $casts = [
        'annee_debut' => 'integer',
        'annee_fin'   => 'integer',
        'statut'      => StatutPlanFormation::class,
    ];

    public function programmes() { return $this->hasMany(ProgrammeFormation::class)->orderBy('annee'); }
    public function actions() { return $this->hasManyThrough(ActionFormation::class, ProgrammeFormation::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }

    public function getPeriodeAttribute(): string
    {
        return $this->annee_debut . '–' . $this->annee_fin;
    }
}
