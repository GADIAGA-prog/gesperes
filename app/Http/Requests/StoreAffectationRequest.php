<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('affectations.create');
    }

    public function rules(): array
    {
        return [
            'agent_id'              => ['required', 'exists:agents,id'],
            'nouvelle_structure_id' => ['required', 'exists:structures,id'],
            'nouvelle_fonction_id'  => ['nullable', 'exists:fonctions,id'],
            'date_effet'            => ['required', 'date'],
            'reference_acte'        => ['nullable', 'string', 'max:120'],
            'motif'                 => ['nullable', 'string', 'max:1000'],
        ];
    }
}
