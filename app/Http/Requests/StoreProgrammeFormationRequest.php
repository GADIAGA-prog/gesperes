<?php

namespace App\Http\Requests;

use App\Enums\StatutPlanFormation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgrammeFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('formations.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'annee'                => ['required', 'integer', 'min:2000', 'max:2100'],
            'objectif_strategique' => ['nullable', 'string', 'max:255'],
            'budget_previsionnel'  => ['required', 'numeric', 'min:0'],
            'statut'               => ['required', Rule::enum(StatutPlanFormation::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'budget_previsionnel'  => 'budget prévisionnel',
            'objectif_strategique' => 'objectif stratégique',
        ];
    }
}
