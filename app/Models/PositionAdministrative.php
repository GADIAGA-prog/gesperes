<?php

namespace App\Models;

use App\Enums\CategoriePosition;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class PositionAdministrative extends Model
{
    use Auditable;

    protected $table = 'positions_administratives';
    protected $fillable = ['code', 'libelle', 'categorie', 'actif'];
    protected $casts = [
        'actif' => 'boolean',
        'categorie' => CategoriePosition::class,
    ];

    public function agents() { return $this->hasMany(Agent::class); }
}
