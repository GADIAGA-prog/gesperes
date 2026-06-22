<?php

namespace App\Enums;

enum TypeDocument: string
{
    case CNIB = 'cnib';
    case DIPLOME = 'diplome';
    case ARRETE = 'arrete';
    case DECISION = 'decision';
    case CONTRAT = 'contrat';
    case ATTESTATION = 'attestation';
    case ACTE_NOMINATION = 'acte_nomination';
    case ACTE_AFFECTATION = 'acte_affectation';
    case CERTIFICAT_MEDICAL = 'certificat_medical';
    case ACTE_NAISSANCE_ENFANT = 'acte_naissance_enfant';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::CNIB => 'CNIB',
            self::DIPLOME => 'Diplôme',
            self::ARRETE => 'Arrêté',
            self::DECISION => 'Décision',
            self::CONTRAT => 'Contrat',
            self::ATTESTATION => 'Attestation',
            self::ACTE_NOMINATION => 'Acte de nomination',
            self::ACTE_AFFECTATION => 'Acte d\'affectation',
            self::CERTIFICAT_MEDICAL => 'Certificat médical',
            self::ACTE_NAISSANCE_ENFANT => 'Acte de naissance enfant',
            self::AUTRE => 'Autre document',
        };
    }

    /** Ce type de document a-t-il typiquement une date d'expiration ? */
    public function expirable(): bool
    {
        return in_array($this, [self::CNIB, self::CERTIFICAT_MEDICAL, self::CONTRAT], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
