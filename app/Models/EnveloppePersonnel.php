<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnveloppePersonnel extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'enveloppes_personnel';

    protected $fillable = ['annee_debut', 'intitule', 'actif'];

    protected $casts = ['actif' => 'boolean', 'annee_debut' => 'integer'];

    public function lignes()
    {
        return $this->hasMany(EnveloppeLigne::class)->orderBy('ordre')->orderBy('id');
    }

    /** Les trois exercices couverts (n+1, n+2, n+3). */
    public function getAnneesAttribute(): array
    {
        return [$this->annee_debut, $this->annee_debut + 1, $this->annee_debut + 2];
    }

    /** Totaux par exercice (somme des lignes). */
    public function getTotauxAttribute(): array
    {
        return [
            (float) $this->lignes->sum('montant_n1'),
            (float) $this->lignes->sum('montant_n2'),
            (float) $this->lignes->sum('montant_n3'),
        ];
    }
}
