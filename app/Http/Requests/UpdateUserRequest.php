<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        $id = $this->route('user')->id;

        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password'     => ['nullable', 'confirmed', Password::min(8)],
            'actif'        => ['nullable', 'boolean'],
            'region'       => ['nullable', 'string', 'max:120'],
            'structure_id' => ['nullable', 'exists:structures,id'],
            'roles'        => ['nullable', 'array'],
            'roles.*'      => ['exists:roles,name'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif')]);
    }
}
