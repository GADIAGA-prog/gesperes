<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaremeSpecifique extends Model
{
    public $timestamps = false;
    protected $table = 'bareme_specifiques';
    protected $fillable = ['emploi_code', 'zone_code', 'montant', 'actif'];
    protected $casts = ['montant' => 'decimal:2', 'actif' => 'boolean'];
}
