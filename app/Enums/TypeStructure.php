<?php

namespace App\Enums;

enum TypeStructure: string
{
    case MINISTERE = 'ministere';
    case PROGRAMME = 'programme';
    case ACTION = 'action';
    case NIVEAU_1 = 'niveau_1';
    case NIVEAU_2 = 'niveau_2';
    case NIVEAU_3 = 'niveau_3';
    case NIVEAU_4 = 'niveau_4';
    case STRUCTURE_OP = 'structure_operationnelle';
    case ETABLISSEMENT = 'etablissement';

    public function label(): string
    {
        return match ($this) {
            self::MINISTERE => 'Ministère',
            self::PROGRAMME => 'Programme',
            self::ACTION => 'Action',
            self::NIVEAU_1 => 'Niveau hiérarchique 1',
            self::NIVEAU_2 => 'Niveau hiérarchique 2',
            self::NIVEAU_3 => 'Niveau hiérarchique 3',
            self::NIVEAU_4 => 'Niveau hiérarchique 4',
            self::STRUCTURE_OP => 'Structure opérationnelle',
            self::ETABLISSEMENT => 'Établissement',
        };
    }

    public function rang(): int
    {
        return match ($this) {
            self::MINISTERE => 0,
            self::PROGRAMME => 1,
            self::ACTION => 2,
            self::NIVEAU_1 => 3,
            self::NIVEAU_2 => 4,
            self::NIVEAU_3 => 5,
            self::NIVEAU_4 => 6,
            self::STRUCTURE_OP => 7,
            self::ETABLISSEMENT => 8,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
