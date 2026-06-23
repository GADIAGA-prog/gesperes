<?php

namespace App\Models;

use App\Enums\EtapeDossier;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Mouvement historisé d'un dossier : trace chaque transmission successive
 * (étape, service et agent destinataires, date et commentaire).
 */
class SuiviDossierEtape extends Model
{
    use Auditable;

    protected $table = 'suivi_dossier_etapes';

    protected $fillable = [
        'suivi_dossier_id', 'etape', 'service_id', 'agent_id',
        'date_mouvement', 'commentaire', 'created_by',
    ];

    protected $casts = [
        'etape'          => EtapeDossier::class,
        'date_mouvement' => 'date',
    ];

    public function dossier() { return $this->belongsTo(SuiviDossier::class, 'suivi_dossier_id'); }
    public function service() { return $this->belongsTo(Structure::class, 'service_id'); }
    public function agent() { return $this->belongsTo(Agent::class, 'agent_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
