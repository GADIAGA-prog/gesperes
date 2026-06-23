<?php

namespace App\Http\Requests;

use App\Enums\ModeIndemnite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreIndemniteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('indemnites.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('indemnite')?->id;

        return [
            'code'            => ['required', 'string', 'max:50', Rule::unique('indemnites', 'code')->ignore($id)],
            'libelle'         => ['required', 'string', 'max:160'],
            'mode'            => ['required', new Enum(ModeIndemnite::class)],
            'valeur'          => ['required', 'numeric', 'min:0'],
            'reference_texte' => ['nullable', 'string', 'max:120'],
            'actif'           => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif')]);
    }
}
