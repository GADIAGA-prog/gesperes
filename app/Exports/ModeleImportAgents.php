<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Modèle vierge à respecter pour l'import d'agents.
 * Les en-têtes correspondent EXACTEMENT à ceux attendus par AgentsImport.
 * Une ligne d'exemple est fournie pour illustrer le format.
 */
class ModeleImportAgents implements FromArray, WithHeadings
{
    /** En-têtes exacts lus par AgentsImport (WithHeadingRow). */
    public const COLONNES = [
        'matricule', 'cle', 'nom', 'prenoms', 'sexe', 'date_naissance',
        'emploi', 'categorie', 'region', 'province', 'commune', 'etablissement',
        'nombre_enfants', 'situation_matrimoniale',
    ];

    public function headings(): array
    {
        return self::COLONNES;
    }

    public function array(): array
    {
        return [
            [
                '123456', 'A', 'OUEDRAOGO', 'Awa', 'F', '15/03/1990',
                'Professeur certifié', 'A', 'Kadiogo', 'CESFPT OUAGA 1', 'Ouagadougou', 'Lycée Municipal',
                2, 'Marié(e)',
            ],
        ];
    }
}
