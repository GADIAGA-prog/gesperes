<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Fonction extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'indemnite_responsabilite', 'actif'];
    protected $casts = ['actif' => 'boolean', 'indemnite_responsabilite' => 'integer'];
}
