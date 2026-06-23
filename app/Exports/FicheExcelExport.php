<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export Excel générique d'une fiche de présence : un titre, des en-têtes,
 * et les lignes déjà mises en forme par le contrôleur.
 */
class FicheExcelExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private string $titre,
        private array $entetes,
        private array $lignes,
    ) {}

    public function title(): string
    {
        return mb_substr($this->titre, 0, 31);
    }

    public function headings(): array
    {
        return $this->entetes;
    }

    public function array(): array
    {
        return $this->lignes;
    }
}
