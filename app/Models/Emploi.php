<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Emploi extends Model
{
    use Auditable;

    protected $table = 'emplois';
    protected $fillable = ['code', 'libelle', 'categorie_id', 'enseignant', 'volume_horaire_defaut', 'actif'];
    protected $casts = [
        'enseignant' => 'boolean',
        'actif' => 'boolean',
        'volume_horaire_defaut' => 'integer',
    ];

    public function categorie() { return $this->belongsTo(Categorie::class); }
    public function agents() { return $this->hasMany(Agent::class); }
}
