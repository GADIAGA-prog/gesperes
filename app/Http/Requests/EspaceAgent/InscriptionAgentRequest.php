<?php

namespace App\Http\Requests\EspaceAgent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class InscriptionAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route publique (invité)
    }

    public function rules(): array
    {
        return [
            'matricule'      => ['required', 'string', 'max:50'],
            'telephone'      => ['required', 'string', 'max:30'],
            'date_naissance' => ['required', 'date'],
            'password'       => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return [
            'matricule'      => 'matricule',
            'telephone'      => 'numéro de téléphone',
            'date_naissance' => 'date de naissance',
            'password'       => 'mot de passe',
        ];
    }
}
