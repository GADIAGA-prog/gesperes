<?php

namespace App\Http\Requests;

use App\Enums\TypeEvenementCarriere;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCarriereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('carriere.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id'              => ['required', 'exists:agents,id'],
            'type'                  => ['required', new Enum(TypeEvenementCarriere::class)],
            'date_effet'            => ['required', 'date'],

            'nouvelle_categorie_id' => ['nullable', 'exists:categories,id', 'required_if:type,promotion'],
            'nouvelle_echelle_id'   => ['nullable', 'exists:echelles,id'],
            'nouvelle_classe_id'    => ['nullable', 'exists:classes,id', 'required_if:type,avancement_classe'],
            'nouvel_echelon_id'     => ['nullable', 'exists:echelons,id', 'required_if:type,avancement_echelon'],
            'nouvelle_fonction_id'  => ['nullable', 'exists:fonctions,id', 'required_if:type,nomination'],
            'nouveau_poste_id'      => ['nullable', 'exists:postes,id'],
            'nouvelle_position_id'  => ['nullable', 'exists:positions_administratives,id', 'required_if:type,changement_position'],

            'reference_acte'        => ['nullable', 'string', 'max:120'],
            'observation'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nouvelle_categorie_id' => 'catégorie',
            'nouvelle_echelle_id'   => 'échelle',
            'nouvelle_classe_id'    => 'classe',
            'nouvel_echelon_id'     => 'échelon',
            'nouvelle_fonction_id'  => 'fonction',
            'nouveau_poste_id'      => 'poste',
            'nouvelle_position_id'  => 'position administrative',
        ];
    }
}
