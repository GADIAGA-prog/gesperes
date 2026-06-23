<?php

namespace App\Http\Requests;

use App\Enums\TypeDiscipline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDossierDisciplinaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('discipline.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id'       => ['required', 'exists:agents,id'],
            'type'           => ['required', new Enum(TypeDiscipline::class)],
            'date_acte'      => ['required', 'date'],
            'reference_acte' => ['nullable', 'string', 'max:120'],
            'motif'          => ['required', 'string', 'max:2000'],
            'nature'         => ['nullable', 'string', 'max:120'],
            'statut'         => ['required', 'in:ouvert,clos'],
            'decision'       => ['nullable', 'string', 'max:2000'],
            'observation'    => ['nullable', 'string', 'max:1000'],
        ];
    }
}
