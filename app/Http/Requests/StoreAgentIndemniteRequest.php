<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentIndemniteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('indemnites.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'indemnite_id' => ['required', 'exists:indemnites,id'],
            'montant'      => ['nullable', 'numeric', 'min:0'],
            'date_debut'   => ['nullable', 'date'],
            'date_fin'     => ['nullable', 'date', 'after_or_equal:date_debut'],
            'observation'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
