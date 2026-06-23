<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNatureDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('suivi.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'code'               => ['nullable', 'string', 'max:30'],
            'libelle'            => ['required', 'string', 'max:120'],
            'delai_defaut_jours' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'actif'              => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'libelle'            => 'libellé',
            'delai_defaut_jours' => 'délai par défaut',
        ];
    }
}
