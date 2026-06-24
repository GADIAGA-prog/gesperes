<?php

namespace App\Enums;

/** Type de compétence requise (guide MFPTPS §III.3.2.2). */
enum TypeCompetence: string
{
    case METIER = 'metier';
    case TRANSVERSALE = 'transversale';
    case COMPORTEMENTALE = 'comportementale';

    public function label(): string
    {
        return match ($this) {
            self::METIER => 'Compétences métiers',
            self::TRANSVERSALE => 'Compétences transversales',
            self::COMPORTEMENTALE => 'Compétences comportementales',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
