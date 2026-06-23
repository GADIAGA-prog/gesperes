<?php

namespace App\Http\Requests;

use App\Enums\EtapeDossier;
use App\Enums\StatutSuiviDossier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuiviDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('suivi.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'reference_bordereau' => ['required', 'string', 'max:120'],
            'structure_id'        => ['required', 'exists:structures,id'],
            'nature_id'           => ['nullable', 'exists:natures_dossier,id'],
            'objet'               => ['nullable', 'string', 'max:255'],
            'etape'               => ['required', Rule::enum(EtapeDossier::class)],
            'statut'              => ['required', Rule::enum(StatutSuiviDossier::class)],
            'service_actuel_id'   => ['nullable', 'exists:structures,id'],
            'agent_actuel_id'     => ['nullable', 'exists:agents,id'],
            'date_reception'      => ['required', 'date'],
            'delai_jours'         => ['required', 'integer', 'min:0', 'max:3650'],
            'date_traitement'     => ['nullable', 'date', 'after_or_equal:date_reception'],
            'observation'         => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reference_bordereau' => 'référence du bordereau',
            'structure_id'        => 'structure',
            'nature_id'           => 'nature du dossier',
            'service_actuel_id'   => 'service actuel',
            'agent_actuel_id'     => 'agent en charge',
            'date_reception'      => 'date de réception',
            'delai_jours'         => 'délai de traitement',
        ];
    }
}
