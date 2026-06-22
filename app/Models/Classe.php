<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use Auditable;

    protected $table = 'classes';
    protected $fillable = ['code', 'libelle', 'actif'];
    protected $casts = ['actif' => 'boolean'];
}
