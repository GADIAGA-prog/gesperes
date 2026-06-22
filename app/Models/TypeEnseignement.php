<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class TypeEnseignement extends Model
{
    use Auditable;

    protected $table = 'type_enseignements';
    protected $fillable = ['code', 'libelle', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function specialites() { return $this->hasMany(Specialite::class); }
}
