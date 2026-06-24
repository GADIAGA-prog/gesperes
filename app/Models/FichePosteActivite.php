<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichePosteActivite extends Model
{
    protected $table = 'fiche_poste_activites';
    protected $fillable = ['fiche_poste_id', 'libelle', 'taux_contribution', 'ordre'];

    public function fichePoste()
    {
        return $this->belongsTo(FichePoste::class);
    }
}
