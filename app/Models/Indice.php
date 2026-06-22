<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Indice extends Model
{
    use Auditable;

    protected $table = 'indices';
    protected $fillable = ['code', 'valeur', 'libelle', 'actif', 'categorie_id', 'classe_id', 'echelon_id'];
    protected $casts = ['actif' => 'boolean', 'valeur' => 'integer'];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function echelon(): BelongsTo
    {
        return $this->belongsTo(Echelon::class);
    }
}
