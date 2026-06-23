<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'domaine', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_competence')
            ->withPivot(['niveau', 'date_acquisition', 'source'])->withTimestamps();
    }
}
