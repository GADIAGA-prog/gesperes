<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Echelon extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'rang', 'actif'];
    protected $casts = ['actif' => 'boolean', 'rang' => 'integer'];
}
