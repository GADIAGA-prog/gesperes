<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaremeTechnicite extends Model
{
    public $timestamps = false;
    protected $table = 'bareme_technicites';
    protected $fillable = ['echelle_code', 'montant', 'actif'];
    protected $casts = ['montant' => 'decimal:2', 'actif' => 'boolean'];
}
