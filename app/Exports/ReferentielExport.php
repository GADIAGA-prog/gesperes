<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export générique d'un référentiel, piloté par sa configuration
 * (App\Support\ReferentielRegistry). Le format de colonnes s'adapte à chaque
 * référentiel : code, libellé, champs spécifiques (catégorie, valeur, …), actif.
 *
 * Les champs « select » (clé étrangère) sont exportés via le CODE de l'élément lié
 * afin que le fichier reste lisible et ré-importable.
 */
class ReferentielExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /** @var array<string,Collection> map id => code pour chaque champ select */
    private array $selectMaps = [];

    public function __construct(private string $type, private array $config)
    {
        foreach ($config['champs'] as $nom => $def) {
            if (($def['type'] ?? null) === 'select' && isset($def['source'])) {
                $this->selectMaps[$nom] = $def['source']::pluck('code', 'id');
            }
        }
    }

    public function title(): string
    {
        return mb_substr($this->config['titre'], 0, 31);
    }

    public function collection(): Collection
    {
        return $this->config['model']::orderBy('code')->get();
    }

    public function headings(): array
    {
        $entetes = self::entetes($this->config);
        if ($this->type === 'indices') {
            $entetes[] = 'salaire_indiciaire';
        }
        return $entetes;
    }

    /** En-têtes attendus pour un référentiel : code, libellé, champs, actif. */
    public static function entetes(array $config): array
    {
        $entetes = ['code', 'libelle'];
        foreach ($config['champs'] as $nom => $def) {
            $entetes[] = str_ends_with($nom, '_id') ? substr($nom, 0, -3) : $nom;
        }
        $entetes[] = 'actif';

        return $entetes;
    }

    public function map($item): array
    {
        $ligne = [$item->code, $item->libelle];
        foreach ($this->config['champs'] as $nom => $def) {
            $ligne[] = $this->valeur($item, $nom, $def);
        }
        $ligne[] = $item->actif ? 'Oui' : 'Non';

        if ($this->type === 'indices') {
            $ligne[] = $item->salaire_indiciaire;
        }

        return $ligne;
    }

    private function valeur($item, string $nom, array $def): mixed
    {
        $v = $item->$nom;

        return match ($def['type']) {
            'select'  => $this->selectMaps[$nom][$v] ?? '',
            'enum'    => is_object($v) ? $v->value : $v,
            'boolean' => $v ? 'Oui' : 'Non',
            default   => $v,
        };
    }
}
