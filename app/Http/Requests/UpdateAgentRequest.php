<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateAgentRequest extends StoreAgentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('agents.update') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $agentId = $this->route('agent')?->id;

        $rules['matricule'] = [
            'required', 'string', 'regex:/^\d+$/',
            Rule::unique('agents', 'matricule')->ignore($agentId)->whereNull('deleted_at'),
        ];

        return $rules;
    }
}
