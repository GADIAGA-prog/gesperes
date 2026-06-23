<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'agent_id', 'periode', 'date_evaluation', 'note', 'objectifs',
        'appreciation', 'evaluateur_id', 'statut', 'created_by',
    ];

    protected $casts = [
        'date_evaluation' => 'date',
        'note' => 'decimal:2',
        'periode' => 'integer',
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function evaluateur() { return $this->belongsTo(User::class, 'evaluateur_id'); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
}
