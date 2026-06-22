<?php

namespace App\Exports;

use App\Models\Agent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AgentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private array $filtres = []) {}

    public function query()
    {
        return Agent::query()
            ->with(['emploi', 'structure', 'categorie'])
            ->recherche($this->filtres['q'] ?? null)
            ->region($this->filtres['region'] ?? null)
            ->orderBy('nom');
    }

    public function headings(): array
    {
        return [
            'Matricule', 'Clé', 'Nom', 'Prénoms', 'Sexe', 'Date naissance',
            'Emploi', 'Catégorie', 'Structure', 'Région', 'Province', 'Commune',
            'Établissement', 'Situation matrimoniale', 'Nombre enfants', 'Statut dossier',
        ];
    }

    public function map($agent): array
    {
        return [
            $agent->matricule,
            $agent->cle,
            $agent->nom,
            $agent->prenoms,
            $agent->sexe?->label(),
            $agent->date_naissance?->format('d/m/Y'),
            $agent->emploi?->libelle,
            $agent->categorie?->code,
            $agent->structure?->libelle,
            $agent->region,
            $agent->province,
            $agent->commune,
            $agent->etablissement,
            $agent->situation_matrimoniale?->label(),
            $agent->nombre_enfants,
            $agent->statut_dossier?->label(),
        ];
    }
}
