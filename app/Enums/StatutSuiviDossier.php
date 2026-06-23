<?php

namespace App\Enums;

/**
 * Statut (cycle de vie) d'un dossier suivi, indépendant de l'étape :
 * pilote notamment le calcul du dépassement de délai (un dossier traité/clos
 * est figé à sa date de traitement).
 */
enum StatutSuiviDossier: string
{
    case EN_COURS  = 'en_cours';
    case EN_ATTENTE = 'en_attente';
    case TRAITE    = 'traite';
    case CLOS      = 'clos';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS   => 'En cours',
            self::EN_ATTENTE => 'En attente',
            self::TRAITE     => 'Traité',
            self::CLOS       => 'Clôturé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EN_COURS   => 'bg-blue-100 text-blue-700',
            self::EN_ATTENTE => 'bg-amber-100 text-amber-800',
            self::TRAITE     => 'bg-green-100 text-green-700',
            self::CLOS       => 'bg-gray-200 text-gray-700',
        };
    }

    /** Le dossier est-il clôturé (plus de traitement attendu) ? */
    public function estTermine(): bool
    {
        return $this === self::TRAITE || $this === self::CLOS;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
