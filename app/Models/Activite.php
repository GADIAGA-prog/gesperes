<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activite extends Model
{
    use Auditable;

    protected $fillable = [
        'exercice', 'code', 'libelle', 'action_id', 'structure_id',
        'code_chapitre', 'libelle_chapitre',
        'objectif_strategique', 'objectif_operationnel', 'indicateur',
        'valeur_initiale', 'cible', 'localite', 'montant',
        'trimestre_1', 'trimestre_2', 'trimestre_3', 'trimestre_4', 'actif',
    ];

    protected $casts = [
        'exercice'    => 'integer',
        'montant'     => 'decimal:2',
        'trimestre_1' => 'decimal:4',
        'trimestre_2' => 'decimal:4',
        'trimestre_3' => 'decimal:4',
        'trimestre_4' => 'decimal:4',
        'actif'       => 'boolean',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(BudgetLigne::class);
    }

    public function getTotalAeAttribute(): float
    {
        return (float) $this->lignes->sum('montant_ae');
    }

    public function getTotalCpAttribute(): float
    {
        return (float) $this->lignes->sum('montant_cp');
    }
}
