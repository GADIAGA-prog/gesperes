<?php

namespace App\Models;

use App\Enums\TypeDocument;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'agent_id', 'type_document', 'reference', 'date_document', 'date_expiration',
        'chemin', 'nom_original', 'mime', 'taille', 'statut', 'commentaire', 'created_by',
    ];

    protected $casts = [
        'date_document' => 'date',
        'date_expiration' => 'date',
        'taille' => 'integer',
        'type_document' => TypeDocument::class,
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->isPast();
    }
}
