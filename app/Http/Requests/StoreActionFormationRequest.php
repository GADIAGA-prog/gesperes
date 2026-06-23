<?php

namespace App\Http\Requests;

use App\Enums\AxeFormation;
use App\Enums\DomaineFormation;
use App\Enums\NiveauCompetence;
use App\Enums\PublicCibleFormation;
use App\Enums\StatutActionFormation;
use App\Enums\StrategieFormation;
use App\Enums\TypeFormationModalite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActionFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('formations.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'programme_formation_id' => ['required', 'exists:programmes_formation,id'],
            'numero_ordre'           => ['nullable', 'integer', 'min:0', 'max:999'],
            'action'                 => ['required', 'string', 'max:255'],
            'theme_module'           => ['nullable', 'string', 'max:255'],
            'type_modalite'          => ['nullable', Rule::enum(TypeFormationModalite::class)],
            'domaine'                => ['nullable', Rule::enum(DomaineFormation::class)],
            'axe'                    => ['nullable', Rule::enum(AxeFormation::class)],
            'strategie'              => ['nullable', Rule::enum(StrategieFormation::class)],
            'niveau_competence'      => ['nullable', Rule::enum(NiveauCompetence::class)],
            'public_cible'           => ['nullable', 'array'],
            'public_cible.*'         => [Rule::enum(PublicCibleFormation::class)],
            'nombre_jours'           => ['required', 'integer', 'min:0', 'max:365'],
            'nombre_agents'          => ['required', 'integer', 'min:0', 'max:100000'],
            'cout'                   => ['required', 'numeric', 'min:0'],
            'source_financement'     => ['nullable', 'string', 'max:120'],
            'statut'                 => ['required', Rule::enum(StatutActionFormation::class)],
            'observation'            => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'programme_formation_id' => 'programme',
            'numero_ordre'           => 'numéro d\'ordre',
            'theme_module'           => 'thème / module',
            'type_modalite'          => 'modalité',
            'niveau_competence'      => 'niveau de compétence',
            'public_cible'           => 'public cible',
            'nombre_jours'           => 'nombre de jours',
            'nombre_agents'          => 'nombre d\'agents',
            'source_financement'     => 'source de financement',
        ];
    }
}
