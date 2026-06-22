<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function emplois() { return $this->hasMany(Emploi::class); }
    public function echelles() { return $this->hasMany(Echelle::class); }
}
