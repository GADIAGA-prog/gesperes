<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMouvementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('mouvements.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id'             => ['required', 'exists:agents,id'],
            'nouvelle_position_id' => ['required', 'exists:positions_administratives,id'],
            'date_effet'           => ['required', 'date'],
            'date_fin'             => ['nullable', 'date', 'after_or_equal:date_effet'],
            'reference_acte'       => ['nullable', 'string', 'max:120'],
            'motif'                => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return ['nouvelle_position_id' => 'nouvelle position'];
    }
}
