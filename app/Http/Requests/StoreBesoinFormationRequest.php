<?php

namespace App\Http\Requests;

use App\Enums\CauseDifficulte;
use App\Enums\DomaineFormation;
use App\Enums\FrequenceTache;
use App\Enums\NiveauMaitrise;
use App\Enums\SolutionBesoin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBesoinFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('formations.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id'        => ['nullable', 'exists:agents,id'],
            'structure_id'    => ['nullable', 'exists:structures,id'],
            'annee_recueil'   => ['required', 'integer', 'min:2000', 'max:2100'],
            'theme_souhaite'  => ['required', 'string', 'max:255'],
            'activite'        => ['nullable', 'string', 'max:1000'],
            'taches'          => ['nullable', 'string', 'max:1000'],
            'difficultes'     => ['nullable', 'string', 'max:1000'],
            'cause'           => ['nullable', Rule::enum(CauseDifficulte::class)],
            'solution'        => ['nullable', Rule::enum(SolutionBesoin::class)],
            'niveau_maitrise' => ['nullable', Rule::enum(NiveauMaitrise::class)],
            'frequence'       => ['nullable', Rule::enum(FrequenceTache::class)],
            'domaine'         => ['nullable', Rule::enum(DomaineFormation::class)],
            'statut'          => ['required', Rule::in(['exprime', 'retenu', 'rejete', 'planifie'])],
            'observation'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'annee_recueil'   => 'année de recueil',
            'theme_souhaite'  => 'thème souhaité',
            'niveau_maitrise' => 'niveau de maîtrise',
        ];
    }
}
