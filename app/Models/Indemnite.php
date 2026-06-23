<?php

namespace App\Models;

use App\Enums\ModeIndemnite;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Indemnite extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'mode', 'bareme', 'valeur', 'reference_texte', 'actif'];

    protected $casts = [
        'mode' => ModeIndemnite::class,
        'valeur' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function attributions() { return $this->hasMany(AgentIndemnite::class); }
}
