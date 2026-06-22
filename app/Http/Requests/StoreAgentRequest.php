<?php

namespace App\Http\Requests;

use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use App\Enums\StatutDossier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('agents.create') ?? false;
    }

    public function rules(): array
    {
        return [
            // Identification
            'matricule' => ['required', 'string', 'regex:/^\d+$/', Rule::unique('agents', 'matricule')->whereNull('deleted_at')],
            'cle' => ['nullable', 'string', 'alpha', 'max:5'],
            'nom' => ['required', 'string', 'max:120'],
            'prenoms' => ['required', 'string', 'max:160'],
            'sexe' => ['required', Rule::in(array_column(Sexe::cases(), 'value'))],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'nationalite' => ['nullable', 'string', 'max:60'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'adresse' => ['nullable', 'string', 'max:255'],

            // Situation administrative
            'statut' => ['nullable', 'string', 'max:60'],
            'emploi_id' => ['nullable', 'exists:emplois,id'],
            'fonction_id' => ['nullable', 'exists:fonctions,id'],
            'poste_id' => ['nullable', 'exists:postes,id'],
            'categorie_id' => ['nullable', 'exists:categories,id'],
            'echelle_id' => ['nullable', 'exists:echelles,id'],
            'classe_id' => ['nullable', 'exists:classes,id'],
            'echelon_id' => ['nullable', 'exists:echelons,id'],
            'indice_id' => ['nullable', 'exists:indices,id'],
            'position_administrative_id' => ['nullable', 'exists:positions_administratives,id'],

            // Affectation
            'structure_id' => ['nullable', 'exists:structures,id'],
            'region' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'commune' => ['nullable', 'string', 'max:80'],
            'etablissement' => ['nullable', 'string', 'max:160'],
            'localite_id' => ['nullable', 'exists:localites,id'],
            'date_affectation' => ['nullable', 'date'],

            // Carrière
            'date_integration' => ['nullable', 'date'],
            'date_effet_emploi' => ['nullable', 'date'],
            'date_nomination' => ['nullable', 'date'],

            // Famille
            'situation_matrimoniale' => ['nullable', Rule::in(array_column(SituationMatrimoniale::cases(), 'value'))],
            'nombre_enfants' => ['nullable', 'integer', 'min:0', 'max:30'],
            'personnes_a_charge' => ['nullable', 'integer', 'min:0', 'max:30'],

            // Enseignement
            'type_enseignement_id' => ['nullable', 'exists:type_enseignements,id'],
            'specialite_id' => ['nullable', 'exists:specialites,id'],
            'volume_horaire_du' => ['nullable', 'integer', 'min:0', 'max:40'],
            'volume_horaire_assure' => ['nullable', 'integer', 'min:0', 'max:40'],

            // Autres
            'distinction_honorifique' => ['nullable', 'string', 'max:160'],
            'observations' => ['nullable', 'string'],
            'statut_dossier' => ['nullable', Rule::in(array_column(StatutDossier::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.regex' => 'Le matricule doit être strictement numérique.',
            'cle.alpha' => 'La clé doit être uniquement alphabétique.',
            'sexe.in' => 'Le sexe doit être M ou F.',
        ];
    }
}
