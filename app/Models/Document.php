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
        'agent_id', 'carriere_evenement_id', 'type_document', 'reference', 'date_document', 'date_expiration',
        'chemin', 'nom_original', 'mime', 'taille', 'statut', 'archive', 'archived_at', 'commentaire', 'created_by',
    ];

    protected $casts = [
        'date_document' => 'date',
        'date_expiration' => 'date',
        'archived_at' => 'datetime',
        'taille' => 'integer',
        'archive' => 'boolean',
        'type_document' => TypeDocument::class,
    ];

    public function agent() { return $this->belongsTo(Agent::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
    public function evenementCarriere() { return $this->belongsTo(CarriereEvenement::class, 'carriere_evenement_id'); }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->isPast();
    }

    /** Taille lisible (Ko / Mo). */
    public function getTailleLisibleAttribute(): string
    {
        $o = (int) $this->taille;
        if ($o >= 1048576) return round($o / 1048576, 1) . ' Mo';
        if ($o >= 1024) return round($o / 1024) . ' Ko';
        return $o . ' o';
    }

    // --- Scopes de recherche documentaire ---
    public function scopeRecherche($query, ?string $terme)
    {
        if (! $terme) {
            return $query;
        }
        return $query->where(function ($q) use ($terme) {
            $q->where('reference', 'like', "%{$terme}%")
              ->orWhere('nom_original', 'like', "%{$terme}%")
              ->orWhere('commentaire', 'like', "%{$terme}%");
        });
    }

    public function scopeExpires($query)
    {
        return $query->whereNotNull('date_expiration')->whereDate('date_expiration', '<', now());
    }
}
