<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTpeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tpee.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'structure_id'         => ['nullable', 'exists:structures,id'],
            'q'                    => ['nullable', 'string', 'max:255'],
            'lignes'               => ['nullable', 'array'],
            'lignes.*'             => ['array'],
            'lignes.*.*.entrees'   => ['nullable', 'integer', 'min:0', 'max:100000'],
            'lignes.*.*.cible'     => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
