<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Indice extends Model
{
    use Auditable;

    protected $table = 'indices';
    protected $fillable = ['code', 'valeur', 'libelle', 'actif', 'categorie_id', 'echelle_id', 'classe_id', 'echelon_id'];
    protected $casts = ['actif' => 'boolean', 'valeur' => 'integer'];

    protected $appends = ['salaire_indiciaire'];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function echelle(): BelongsTo
    {
        return $this->belongsTo(Echelle::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function echelon(): BelongsTo
    {
        return $this->belongsTo(Echelon::class);
    }

    /**
     * Salaire indiciaire mensuel = indice × point_annuel / mois_par_an.
     * Formule en vigueur : indice × 2331 / 12 (paramétrable, config/grille.php).
     */
    public function getSalaireIndiciaireAttribute(): ?float
    {
        $point = config('grille.point_annuel');
        $mois = (int) config('grille.mois_par_an', 12);

        if ($point === null || $this->valeur === null || $mois === 0) {
            return null;
        }

        return round($this->valeur * (float) $point / $mois, 2);
    }
}
