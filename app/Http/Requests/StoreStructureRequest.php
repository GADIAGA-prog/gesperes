<?php

namespace App\Http\Requests;

use App\Enums\TypeStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('structures.create');
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50', Rule::unique('structures', 'code')->whereNull('deleted_at')],
            'libelle'     => ['required', 'string', 'max:255'],
            'type'        => ['required', new Enum(TypeStructure::class)],
            'parent_id'   => ['nullable', 'exists:structures,id'],
            'action_id'   => ['nullable', 'exists:actions,id'],
            'region_id'   => ['nullable', 'exists:regions,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'localite_id' => ['nullable', 'exists:localites,id'],
            'responsable_agent_id' => ['nullable', 'exists:agents,id'],
            'actif'       => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif')]);
    }
}
