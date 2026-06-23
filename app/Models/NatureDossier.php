<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Référentiel paramétrable des natures de dossier (ex : avancement, mutation,
 * congé, retraite…). Chaque nature peut porter un délai de traitement par défaut
 * proposé à la création d'un dossier.
 */
class NatureDossier extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'natures_dossier';

    protected $fillable = [
        'mpp_procedure_id', 'code', 'libelle', 'delai_defaut_jours', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'delai_defaut_jours' => 'integer',
    ];

    public function dossiers() { return $this->hasMany(SuiviDossier::class, 'nature_id'); }

    /** Procédure source du référentiel MPP GRH (le cas échéant). */
    public function procedureMpp() { return $this->belongsTo(MppProcedure::class, 'mpp_procedure_id'); }
}
