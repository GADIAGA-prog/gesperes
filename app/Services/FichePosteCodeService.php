<?php

namespace App\Services;

use App\Enums\TypePoste;
use App\Models\FichePoste;

/**
 * Codification automatique des fiches de poste (guide MFPTPS §III.3.1.1).
 *
 *  - Opérationnel : FAMPRO-EMPLOITYPE-POSTE-CHIFFRE   ex. GRH-RT-CPR-4
 *  - Fonction     : FAMPRO-POSTE-CHIFFRE              ex. GRH-DGFP-1 (sigle seul si hors hiérarchie)
 *  - Soutien      : POSTE-CHIFFRE                     ex. COND-4
 */
class FichePosteCodeService
{
    /** Mots vides ignorés pour dériver le sigle d'un intitulé. */
    private const VIDES = [
        'de', 'des', 'du', 'la', 'le', 'les', 'un', 'une', 'et', 'en',
        'au', 'aux', 'a', 'à', 'sur', 'pour', 'par', 'd', 'l',
    ];

    public function generer(FichePoste $fiche): string
    {
        $sigle = $this->sigle($fiche->intitule);
        $chiffre = $fiche->position_hierarchique?->chiffre();

        $parts = match ($fiche->type_poste) {
            TypePoste::SOUTIEN => [$sigle, $chiffre],
            TypePoste::FONCTION => [$fiche->familleProfessionnelle?->code, $sigle, $chiffre],
            default => [$fiche->familleProfessionnelle?->code, $fiche->emploiType?->code, $sigle, $chiffre],
        };

        return collect($parts)
            ->filter(fn ($p) => $p !== null && $p !== '')
            ->implode('-');
    }

    /**
     * Sigle significatif de l'intitulé :
     *  - plusieurs mots significatifs → initiales (« Chargé de la planification du recrutement » → CPR ; « Agent de liaison » → AL) ;
     *  - un seul mot significatif → 4 premières lettres (« Conducteur » → COND).
     */
    public function sigle(?string $intitule): string
    {
        $mots = preg_split('/[\s\-\']+/u', mb_strtolower(trim((string) $intitule)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $significatifs = collect($mots)->reject(fn ($mot) => in_array($mot, self::VIDES, true))->values();

        if ($significatifs->isEmpty()) {
            return mb_strtoupper(mb_substr(preg_replace('/\s+/', '', (string) $intitule), 0, 4));
        }

        if ($significatifs->count() === 1) {
            return mb_strtoupper(mb_substr($significatifs->first(), 0, 4));
        }

        return $significatifs->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)))->implode('');
    }
}
