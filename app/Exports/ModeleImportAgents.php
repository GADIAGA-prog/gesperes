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
    /**
     * En-têtes exacts lus par AgentsImport (WithHeadingRow).
     * Le bloc rattachement (niveau_1 … service) est aligné sur l'export :
     * la structure est résolue par la cascade, jamais par région/province/commune.
     */
    public const COLONNES = [
        'matricule', 'cle', 'nom', 'prenoms', 'sexe', 'date_naissance',
        'emploi', 'categorie',
        'niveau_1', 'niveau_2', 'niveau_3', 'niveau_4', 'structure', 'service',
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
                'Professeur certifié', 'A',
                'MESFPTT', 'Secrétariat général', 'DRESFPT-Liptako', '', 'Secrétariat général', 'DRESFPT-Liptako',
                2, 'Marié(e)',
            ],
        ];
    }
}
