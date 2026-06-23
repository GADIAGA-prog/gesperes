<?php

namespace App\Models;

use App\Enums\EtapeDossier;
use App\Enums\StatutSuiviDossier;
use App\Services\SuiviDossierService;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Dossier administratif suivi le long de son circuit de traitement.
 * La localisation courante (service_actuel_id / agent_actuel_id) et l'étape
 * indiquent « à quel niveau se situe le dossier ». Le délai de traitement permet
 * de déterminer si l'instruction est dans les temps ou en retard.
 */
class SuiviDossier extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'suivi_dossiers';

    protected $fillable = [
        'reference_bordereau', 'structure_id', 'nature_id', 'objet',
        'etape', 'statut', 'service_actuel_id', 'agent_actuel_id',
        'date_reception', 'delai_jours', 'date_traitement', 'observation', 'created_by',
    ];

    protected $casts = [
        'etape'           => EtapeDossier::class,
        'statut'          => StatutSuiviDossier::class,
        'date_reception'  => 'date',
        'date_traitement' => 'date',
        'delai_jours'     => 'integer',
    ];

    /* ── Relations ─────────────────────────────────────────── */
    public function structure() { return $this->belongsTo(Structure::class); }
    public function nature() { return $this->belongsTo(NatureDossier::class, 'nature_id'); }
    public function serviceActuel() { return $this->belongsTo(Structure::class, 'service_actuel_id'); }
    public function agentActuel() { return $this->belongsTo(Agent::class, 'agent_actuel_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
    public function etapes() { return $this->hasMany(SuiviDossierEtape::class)->orderBy('date_mouvement')->orderBy('id'); }

    /* ── Attributs calculés (délégués au service métier) ───── */

    /** Date limite de traitement = date de réception + délai accordé. */
    public function getDateLimiteAttribute(): ?\Illuminate\Support\Carbon
    {
        return app(SuiviDossierService::class)->dateLimite($this);
    }

    /** Le délai de traitement est-il dépassé ? */
    public function getEnRetardAttribute(): bool
    {
        return app(SuiviDossierService::class)->estEnRetard($this);
    }

    /** Jours restants avant l'échéance (négatif si dépassé), null si pas de délai. */
    public function getJoursRestantsAttribute(): ?int
    {
        return app(SuiviDossierService::class)->joursRestants($this);
    }
}
