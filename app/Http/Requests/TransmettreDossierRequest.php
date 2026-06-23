<?php

namespace App\Http\Requests;

use App\Enums\EtapeDossier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransmettreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('suivi.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'etape'          => ['required', Rule::enum(EtapeDossier::class)],
            'service_id'     => ['nullable', 'exists:structures,id'],
            'agent_id'       => ['nullable', 'exists:agents,id'],
            'date_mouvement' => ['required', 'date'],
            'commentaire'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id'     => 'service destinataire',
            'agent_id'       => 'agent destinataire',
            'date_mouvement' => 'date du mouvement',
        ];
    }
}
