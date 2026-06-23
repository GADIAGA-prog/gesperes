<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pointage extends Model
{
    use Auditable;

    protected $table = 'pointages';
    protected $fillable = [
        'agent_id', 'structure_id', 'date_pointage', 'present',
        'heure_arrivee', 'heure_depart', 'motif_absence_id',
        'duree_jours', 'duree_heures', 'reference_piece', 'mesure_prise',
        'observation', 'saisi_par',
    ];

    protected $casts = [
        'date_pointage' => 'date',
        'present' => 'boolean',
        'duree_jours' => 'decimal:2',
        'duree_heures' => 'decimal:2',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function motifAbsence(): BelongsTo
    {
        return $this->belongsTo(MotifAbsence::class);
    }

    /** Absence sans motif renseigné ⇒ injustifiée. */
    public function getEstInjustifieeAttribute(): bool
    {
        return ! $this->present && $this->motif_absence_id === null;
    }

    public function scopeAbsents(Builder $query): Builder
    {
        return $query->where('present', false);
    }

    public function scopePeriode(Builder $query, string $debut, string $fin): Builder
    {
        return $query->whereBetween('date_pointage', [$debut, $fin]);
    }
}
