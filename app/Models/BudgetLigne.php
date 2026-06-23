<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLigne extends Model
{
    use Auditable;

    protected $table = 'budget_lignes';

    protected $fillable = [
        'activite_id', 'exercice', 'code_article', 'code_paragraphe',
        'libelle_categorie', 'montant_ae', 'montant_cp',
    ];

    protected $casts = [
        'exercice'   => 'integer',
        'montant_ae' => 'decimal:2',
        'montant_cp' => 'decimal:2',
    ];

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }
}
