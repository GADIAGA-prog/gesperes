<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class FamilleProfessionnelle extends Model
{
    use Auditable;

    protected $table = 'familles_professionnelles';
    protected $fillable = ['code', 'libelle', 'metier', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function emploisTypes()
    {
        return $this->hasMany(EmploiType::class);
    }
}
