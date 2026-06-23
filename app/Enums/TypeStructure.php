<?php

namespace App\Enums;

/**
 * Nature d'une structure (et non son niveau hiérarchique : la profondeur est
 * désormais déduite dynamiquement de la chaîne des structures parentes).
 */
enum TypeStructure: string
{
    case MINISTERE = 'ministere';
    case DIRECTION = 'direction';
    case SERVICE = 'service';
    case ETABLISSEMENT = 'etablissement';

    public function label(): string
    {
        return match ($this) {
            self::MINISTERE => 'Ministère',
            self::DIRECTION => 'Direction',
            self::SERVICE => 'Service',
            self::ETABLISSEMENT => 'Établissement',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
