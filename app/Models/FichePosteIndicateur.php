<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichePosteIndicateur extends Model
{
    protected $table = 'fiche_poste_indicateurs';
    protected $fillable = ['fiche_poste_id', 'libelle', 'nature', 'ordre'];

    public function fichePoste()
    {
        return $this->belongsTo(FichePoste::class);
    }
}
