<?php

namespace App\Models;

use App\Enums\CauseDifficulte;
use App\Enums\DomaineFormation;
use App\Enums\FrequenceTache;
use App\Enums\NiveauMaitrise;
use App\Enums\SolutionBesoin;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Besoin de formation recueilli auprès d'un agent (fiche de recueil — Annexe 1).
 * Sert de matière première à l'élaboration des actions du plan : un besoin retenu
 * peut être rattaché à une action de formation.
 */
class BesoinFormation extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'besoins_formation';

    protected $fillable = [
        'agent_id', 'structure_id', 'annee_recueil', 'theme_souhaite',
        'activite', 'taches', 'difficultes', 'cause', 'solution',
        'niveau_maitrise', 'frequence', 'domaine', 'statut',
        'action_formation_id', 'observation', 'created_by',
    ];

    protected $casts = [
        'annee_recueil'   => 'integer',
        'cause'           => CauseDifficulte::class,
        'solution'        => SolutionBesoin::class,
        'niveau_maitrise' => NiveauMaitrise::class,
        'frequence'       => FrequenceTache::class,
        'domaine'         => DomaineFormation::class,
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function structure() { return $this->belongsTo(Structure::class); }
    public function action() { return $this->belongsTo(ActionFormation::class, 'action_formation_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
