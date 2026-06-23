<?php

namespace App\Http\Requests;

use App\Enums\StatutPlanFormation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('formations.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'intitule'    => ['required', 'string', 'max:180'],
            'annee_debut' => ['required', 'integer', 'min:2000', 'max:2100'],
            'annee_fin'   => ['required', 'integer', 'min:2000', 'max:2100', 'gte:annee_debut'],
            'vision'      => ['nullable', 'string', 'max:2000'],
            'finalite'    => ['nullable', 'string', 'max:2000'],
            'objectifs'   => ['nullable', 'string', 'max:4000'],
            'statut'      => ['required', Rule::enum(StatutPlanFormation::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'annee_debut' => 'année de début',
            'annee_fin'   => 'année de fin',
        ];
    }
}
