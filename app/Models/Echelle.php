<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Echelle extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'categorie_id', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function categorie() { return $this->belongsTo(Categorie::class); }
}
