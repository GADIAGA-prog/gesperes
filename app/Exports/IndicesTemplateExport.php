<?php

namespace App\Exports;

use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Modèle de fichier d'import des indices.
 * Fournit les en-têtes attendus et une ligne d'exemple construite à partir
 * des référentiels réellement présents (pour que l'utilisateur voie des codes valides).
 */
class IndicesTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Indices';
    }

    public function headings(): array
    {
        return ['categorie', 'classe', 'echelon', 'valeur', 'code', 'libelle'];
    }

    public function array(): array
    {
        $cat = Categorie::orderBy('code')->value('code') ?? 'A';
        $classe = Classe::orderBy('code')->value('code') ?? 'C1';
        $echelon = Echelon::orderBy('rang')->value('code') ?? 'E1';

        return [
            // categorie, classe, echelon, valeur, code (optionnel), libelle (optionnel)
            [$cat, $classe, $echelon, 350, '', ''],
        ];
    }
}
