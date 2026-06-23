<?php

namespace App\Enums;

/** Niveau de maîtrise actuel d'une tâche (échelle de la fiche de recueil, Annexe 1). */
enum NiveauMaitrise: string
{
    case REPRODUCTION    = 'reproduction';
    case AUTONOMIE       = 'autonomie';
    case GESTION_COURANTE = 'gestion_courante';
    case EXPERTISE       = 'expertise';

    public function label(): string
    {
        return match ($this) {
            self::REPRODUCTION    => 'Reproduit sous contrôle (sans maîtrise des principes)',
            self::AUTONOMIE       => 'Applique de façon autonome',
            self::GESTION_COURANTE => 'Applique en gestion courante et autres situations',
            self::EXPERTISE       => 'Exerce un jugement critique, anticipe, innove et forme',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
