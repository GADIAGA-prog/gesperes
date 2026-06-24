<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichePosteValidation extends Model
{
    protected $table = 'fiche_poste_validations';
    protected $fillable = ['fiche_poste_id', 'etape', 'user_id', 'version', 'commentaire'];

    public function fichePoste()
    {
        return $this->belongsTo(FichePoste::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Libellé lisible de l'étape. */
    public function etapeLabel(): string
    {
        return match ($this->etape) {
            'soumission' => 'Validée par le supérieur immédiat',
            'adoption'   => 'Adoptée (DRH / comité de pilotage)',
            'revision'   => 'Mise en révision',
            default      => ucfirst($this->etape),
        };
    }
}
