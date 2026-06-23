<?php

namespace App\Models;

use App\Enums\StatutConge;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conge extends Model
{
    use Auditable;

    protected $table = 'conges';
    protected $fillable = [
        'agent_id', 'motif_absence_id', 'date_debut', 'date_fin', 'nombre_jours',
        'statut', 'motif', 'reference_decision', 'observation',
        'valide_par', 'date_validation', 'saisi_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_validation' => 'date',
        'nombre_jours' => 'integer',
        'statut' => StatutConge::class,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function motifAbsence(): BelongsTo
    {
        return $this->belongsTo(MotifAbsence::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /** Demandes validées (imputées sur les soldes). */
    public function scopeValide(Builder $query): Builder
    {
        return $query->where('statut', StatutConge::VALIDE->value);
    }

    /** Demandes chevauchant l'année civile donnée. */
    public function scopeAnnee(Builder $query, int $annee): Builder
    {
        return $query->whereYear('date_debut', $annee);
    }
}
