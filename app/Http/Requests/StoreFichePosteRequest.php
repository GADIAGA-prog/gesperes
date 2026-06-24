<?php

namespace App\Http\Requests;

use App\Enums\NiveauCompetencePoste;
use App\Enums\PositionHierarchique;
use App\Enums\PositionMission;
use App\Enums\StatutFichePoste;
use App\Enums\TypeCompetence;
use App\Enums\TypePoste;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreFichePosteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fiches-poste.manage');
    }

    public function rules(): array
    {
        return [
            'code'        => ['nullable', 'string', 'max:50'],
            'intitule'    => ['required', 'string', 'max:255'],
            'type_poste'  => ['required', new Enum(TypePoste::class)],
            'position_mission'      => ['nullable', new Enum(PositionMission::class)],
            'position_hierarchique' => ['nullable', new Enum(PositionHierarchique::class)],

            'famille_professionnelle_id' => ['nullable', 'exists:familles_professionnelles,id'],
            'emploi_type_id' => ['nullable', 'exists:emplois_types,id'],
            'emploi_id'      => ['nullable', 'exists:emplois,id'],
            'famille_emplois' => ['nullable', 'string', 'max:255'],
            'categorie_id'   => ['nullable', 'exists:categories,id'],
            'structure_id'   => ['nullable', 'exists:structures,id'],

            'mission' => ['nullable', 'string'],
            'niveau_hierarchique_superieur' => ['nullable', 'string', 'max:255'],
            'niveau_hierarchique_inferieur' => ['nullable', 'string', 'max:255'],
            'relations_internes' => ['nullable', 'string'],
            'relations_externes' => ['nullable', 'string'],

            'moyens_generaux'    => ['nullable', 'string'],
            'moyens_specifiques' => ['nullable', 'string'],

            'niveau_etudes'  => ['nullable', 'string', 'max:255'],
            'domaine'        => ['nullable', 'string', 'max:255'],
            'specialite'     => ['nullable', 'string', 'max:255'],
            'experience_pro' => ['nullable', 'string', 'max:255'],

            'statut'  => ['nullable', new Enum(StatutFichePoste::class)],
            'version' => ['nullable', 'string', 'max:50'],

            'activites'                  => ['nullable', 'array'],
            'activites.*.libelle'        => ['nullable', 'string', 'max:255'],
            'activites.*.taux_contribution' => ['nullable', 'string', 'max:50'],

            'indicateurs'                => ['nullable', 'array'],
            'indicateurs.*.libelle'      => ['nullable', 'string', 'max:255'],
            'indicateurs.*.nature'       => ['nullable', 'string', 'max:50'],

            'competences'                => ['nullable', 'array'],
            'competences.*.competence_id' => ['nullable', 'exists:competences,id'],
            'competences.*.type'   => ['nullable', new Enum(TypeCompetence::class)],
            'competences.*.niveau' => ['nullable', new Enum(NiveauCompetencePoste::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'intitule'   => 'intitulé du poste',
            'type_poste' => 'type de poste',
        ];
    }
}
