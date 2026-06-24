<?php

namespace App\Models;

use App\Enums\PositionHierarchique;
use App\Enums\PositionMission;
use App\Enums\StatutFichePoste;
use App\Enums\TypePoste;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FichePoste extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'fiches_poste';

    protected $fillable = [
        'code', 'intitule', 'type_poste', 'position_mission', 'position_hierarchique',
        'famille_professionnelle_id', 'emploi_type_id', 'emploi_id', 'famille_emplois',
        'categorie_id', 'structure_id', 'mission',
        'niveau_hierarchique_superieur', 'niveau_hierarchique_inferieur',
        'relations_internes', 'relations_externes',
        'moyens_generaux', 'moyens_specifiques',
        'niveau_etudes', 'domaine', 'specialite', 'experience_pro',
        'statut', 'version', 'adoptee_at', 'created_by',
    ];

    protected $casts = [
        'type_poste' => TypePoste::class,
        'position_mission' => PositionMission::class,
        'position_hierarchique' => PositionHierarchique::class,
        'statut' => StatutFichePoste::class,
        'adoptee_at' => 'datetime',
    ];

    // --- Relations ---
    public function familleProfessionnelle() { return $this->belongsTo(FamilleProfessionnelle::class); }
    public function emploiType() { return $this->belongsTo(EmploiType::class); }
    public function emploi() { return $this->belongsTo(Emploi::class); }
    public function categorie() { return $this->belongsTo(Categorie::class); }
    public function structure() { return $this->belongsTo(Structure::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }

    public function activites() { return $this->hasMany(FichePosteActivite::class)->orderBy('ordre'); }
    public function indicateurs() { return $this->hasMany(FichePosteIndicateur::class)->orderBy('ordre'); }

    public function competences()
    {
        return $this->belongsToMany(Competence::class, 'fiche_poste_competence')
            ->withPivot(['type', 'niveau'])->withTimestamps();
    }

    public function validations() { return $this->hasMany(FichePosteValidation::class)->orderByDesc('id'); }
    public function titulaires() { return $this->hasMany(Agent::class); }

    // --- Garde-fous du workflow (guide §IV) ---
    public function peutSoumettre(): bool { return $this->statut === StatutFichePoste::BROUILLON; }
    public function peutAdopter(): bool { return $this->statut === StatutFichePoste::VALIDEE_SUPERIEUR; }
    public function peutReviser(): bool { return $this->statut === StatutFichePoste::ADOPTEE; }
    public function estModifiable(): bool { return $this->statut !== StatutFichePoste::ADOPTEE; }
}
