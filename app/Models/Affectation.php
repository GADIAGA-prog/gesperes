<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use Auditable;

    protected $fillable = [
        'agent_id', 'ancienne_structure_id', 'nouvelle_structure_id',
        'ancienne_fonction_id', 'nouvelle_fonction_id', 'date_effet',
        'reference_acte', 'motif', 'document_path', 'created_by',
    ];

    protected $casts = ['date_effet' => 'date'];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function ancienneStructure() { return $this->belongsTo(Structure::class, 'ancienne_structure_id'); }
    public function nouvelleStructure() { return $this->belongsTo(Structure::class, 'nouvelle_structure_id'); }
    public function ancienneFonction() { return $this->belongsTo(Fonction::class, 'ancienne_fonction_id'); }
    public function nouvelleFonction() { return $this->belongsTo(Fonction::class, 'nouvelle_fonction_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
