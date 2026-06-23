<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaremeLogement extends Model
{
    public $timestamps = false;
    protected $table = 'bareme_logements';
    protected $fillable = ['categorie_code', 'enseignant', 'en_classe', 'montant', 'actif'];
    protected $casts = ['enseignant' => 'boolean', 'en_classe' => 'boolean', 'montant' => 'decimal:2', 'actif' => 'boolean'];
}
