<?php

namespace App\Models;

use App\Enums\LieuExercice;
use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use App\Enums\StatutDossier;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use Auditable, SoftDeletes, HasFactory;

    protected $fillable = [
        'matricule', 'cle', 'nom', 'prenoms', 'sexe', 'date_naissance',
        'nationalite', 'telephone', 'email', 'adresse',
        'statut', 'emploi_id', 'fonction_id', 'poste_id', 'categorie_id',
        'echelle_id', 'classe_id', 'echelon_id', 'indice_id', 'position_administrative_id',
        'structure_id', 'region_id', 'province_id', 'region', 'province', 'commune', 'etablissement', 'localite_id', 'date_affectation',
        'date_integration', 'date_effet_emploi', 'date_nomination', 'date_retraite',
        'situation_matrimoniale', 'nombre_enfants', 'personnes_a_charge', 'allocation_familiale',
        'type_enseignement_id', 'specialite_id', 'volume_horaire_du', 'volume_horaire_assure', 'lieu_exercice',
        'distinction_honorifique', 'observations', 'statut_dossier',
        'user_id', 'created_by',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_affectation' => 'date',
        'date_integration' => 'date',
        'date_effet_emploi' => 'date',
        'date_nomination' => 'date',
        'date_retraite' => 'date',
        'allocation_familiale' => 'decimal:2',
        'nombre_enfants' => 'integer',
        'personnes_a_charge' => 'integer',
        'volume_horaire_du' => 'integer',
        'volume_horaire_assure' => 'integer',
        'sexe' => Sexe::class,
        'situation_matrimoniale' => SituationMatrimoniale::class,
        'statut_dossier' => StatutDossier::class,
        'lieu_exercice' => LieuExercice::class,
    ];

    // --- Relations ---
    public function emploi() { return $this->belongsTo(Emploi::class); }
    public function fonction() { return $this->belongsTo(Fonction::class); }
    public function poste() { return $this->belongsTo(Poste::class); }
    public function categorie() { return $this->belongsTo(Categorie::class); }
    public function echelle() { return $this->belongsTo(Echelle::class); }
    public function classe() { return $this->belongsTo(Classe::class); }
    public function echelon() { return $this->belongsTo(Echelon::class); }
    public function indice() { return $this->belongsTo(Indice::class); }
    public function positionAdministrative() { return $this->belongsTo(PositionAdministrative::class); }
    public function structure() { return $this->belongsTo(Structure::class); }
    public function region() { return $this->belongsTo(Region::class); }
    public function province() { return $this->belongsTo(Province::class); }
    public function localite() { return $this->belongsTo(Localite::class); }
    public function typeEnseignement() { return $this->belongsTo(TypeEnseignement::class); }
    public function specialite() { return $this->belongsTo(Specialite::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
    public function documents() { return $this->hasMany(Document::class); }
    public function affectations() { return $this->hasMany(Affectation::class)->latest('date_effet'); }
    public function evenementsCarriere() { return $this->hasMany(CarriereEvenement::class)->latest('date_effet'); }
    public function mouvements() { return $this->hasMany(Mouvement::class)->latest('date_effet'); }
    public function dernierMouvement() { return $this->hasOne(Mouvement::class)->latestOfMany('date_effet'); }
    public function indemnites() { return $this->hasMany(AgentIndemnite::class); }
    public function formations() { return $this->belongsToMany(Formation::class, 'formation_agent')->withPivot(['resultat', 'observation'])->withTimestamps(); }
    public function dossiersDisciplinaires() { return $this->hasMany(DossierDisciplinaire::class)->latest('date_acte'); }
    public function competences() { return $this->belongsToMany(Competence::class, 'agent_competence')->withPivot(['niveau', 'date_acquisition', 'source'])->withTimestamps(); }
    public function evaluations() { return $this->hasMany(Evaluation::class)->latest('date_evaluation'); }
    public function conges() { return $this->hasMany(Conge::class)->latest('date_debut'); }
    public function pointages() { return $this->hasMany(Pointage::class)->latest('date_pointage'); }

    // --- Accessors ---
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->nom} {$this->prenoms}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance?->age;
    }

    /** Agent considéré actif si sa position administrative est de famille "Activité". */
    public function getEstActifAttribute(): bool
    {
        $cat = $this->positionAdministrative?->categorie;
        return $cat === null ? true : $cat->estActif();
    }

    // --- Scopes ---
    public function scopeRecherche(Builder $query, ?string $terme): Builder
    {
        if (! $terme) {
            return $query;
        }
        return $query->where(function (Builder $q) use ($terme) {
            $q->where('matricule', 'like', "%{$terme}%")
              ->orWhere('nom', 'like', "%{$terme}%")
              ->orWhere('prenoms', 'like', "%{$terme}%");
        });
    }

    public function scopeRegion(Builder $query, ?string $region): Builder
    {
        return $region ? $query->where('region', $region) : $query;
    }
}
