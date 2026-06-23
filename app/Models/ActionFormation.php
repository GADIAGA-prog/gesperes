<?php

namespace App\Models;

use App\Enums\AxeFormation;
use App\Enums\DomaineFormation;
use App\Enums\NiveauCompetence;
use App\Enums\PublicCibleFormation;
use App\Enums\StatutActionFormation;
use App\Enums\StrategieFormation;
use App\Enums\TypeFormationModalite;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Action de formation planifiée — une ligne de la « Présentation synthétique des
 * actions » du plan (champs numérotés 1→7). Peut être réalisée par une ou
 * plusieurs sessions (table `formations`).
 */
class ActionFormation extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'actions_formation';

    protected $fillable = [
        'programme_formation_id', 'numero_ordre', 'action', 'theme_module',
        'type_modalite', 'domaine', 'axe', 'strategie', 'niveau_competence',
        'public_cible', 'nombre_jours', 'nombre_agents', 'cout',
        'source_financement', 'statut', 'observation',
    ];

    protected $casts = [
        'numero_ordre'      => 'integer',
        'nombre_jours'      => 'integer',
        'nombre_agents'     => 'integer',
        'cout'              => 'decimal:2',
        'public_cible'      => 'array',
        'type_modalite'     => TypeFormationModalite::class,
        'domaine'           => DomaineFormation::class,
        'axe'               => AxeFormation::class,
        'strategie'         => StrategieFormation::class,
        'niveau_competence' => NiveauCompetence::class,
        'statut'            => StatutActionFormation::class,
    ];

    public function programme() { return $this->belongsTo(ProgrammeFormation::class, 'programme_formation_id'); }
    public function sessions() { return $this->hasMany(Formation::class, 'action_formation_id'); }
    public function besoins() { return $this->hasMany(BesoinFormation::class, 'action_formation_id'); }

    /** Libellés lisibles du public cible (multi-sélection). */
    public function getPublicCibleLabelAttribute(): string
    {
        return PublicCibleFormation::labelsFor($this->public_cible);
    }
}
