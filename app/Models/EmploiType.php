<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class EmploiType extends Model
{
    use Auditable;

    protected $table = 'emplois_types';
    protected $fillable = ['code', 'libelle', 'famille_professionnelle_id', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function familleProfessionnelle()
    {
        return $this->belongsTo(FamilleProfessionnelle::class);
    }
}
