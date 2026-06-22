<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Poste extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'actif'];
    protected $casts = ['actif' => 'boolean'];
}
