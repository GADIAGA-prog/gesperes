<?php

namespace App\Models;

use App\Enums\TypeDiscipline;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierDisciplinaire extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'dossiers_disciplinaires';

    protected $fillable = [
        'agent_id', 'type', 'date_acte', 'reference_acte', 'motif',
        'nature', 'statut', 'decision', 'observation', 'created_by',
    ];

    protected $casts = [
        'date_acte' => 'date',
        'type' => TypeDiscipline::class,
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
