<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrevisionEffectif extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'previsions_effectifs';

    protected $fillable = [
        'emploi_id', 'annee', 'structure_id', 'entrees_prevues', 'effectif_cible',
        'observation', 'created_by',
    ];

    protected $casts = [
        'annee'           => 'integer',
        'entrees_prevues' => 'integer',
        'effectif_cible'  => 'integer',
    ];

    public function emploi() { return $this->belongsTo(Emploi::class); }
    public function structure() { return $this->belongsTo(Structure::class); }
}
