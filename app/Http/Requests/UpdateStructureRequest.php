<?php

namespace App\Http\Requests;

use App\Enums\TypeStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('structures.update');
    }

    public function rules(): array
    {
        $id = $this->route('structure')->id;

        return [
            'code'        => ['required', 'string', 'max:50', Rule::unique('structures', 'code')->ignore($id)->whereNull('deleted_at')],
            'libelle'     => ['required', 'string', 'max:255'],
            'type'        => ['required', new Enum(TypeStructure::class)],
            'parent_id'   => ['nullable', 'exists:structures,id', 'not_in:' . $id],
            'action_id'   => ['nullable', 'exists:actions,id'],
            'region_id'   => ['nullable', 'exists:regions,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'localite_id' => ['nullable', 'exists:localites,id'],
            'actif'       => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif')]);
    }
}
