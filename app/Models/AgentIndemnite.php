<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class AgentIndemnite extends Model
{
    use Auditable;

    protected $table = 'agent_indemnites';

    protected $fillable = [
        'agent_id', 'indemnite_id', 'montant', 'date_debut', 'date_fin',
        'actif', 'observation', 'created_by',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean',
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function indemnite() { return $this->belongsTo(Indemnite::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
