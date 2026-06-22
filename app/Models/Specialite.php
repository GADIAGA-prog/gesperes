<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'type_enseignement_id', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function typeEnseignement() { return $this->belongsTo(TypeEnseignement::class); }
}
