<?php

namespace App\Services;

use App\Enums\StatutFichePoste;
use App\Models\FichePoste;

/**
 * Workflow de validation des fiches de poste (guide MFPTPS §IV) :
 * brouillon → validée (supérieur immédiat) → adoptée (DRH / comité).
 * La révision d'une fiche adoptée la repasse en brouillon avec une nouvelle version.
 * Chaque transition est historisée dans fiche_poste_validations.
 */
class FichePosteWorkflowService
{
    /** brouillon → validée par le supérieur immédiat. */
    public function soumettre(FichePoste $fiche, ?int $userId = null, ?string $commentaire = null): void
    {
        $fiche->update(['statut' => StatutFichePoste::VALIDEE_SUPERIEUR]);
        $this->journaliser($fiche, 'soumission', $userId, $commentaire);
    }

    /** validée → adoptée (DRH / comité de pilotage). */
    public function adopter(FichePoste $fiche, ?int $userId = null, ?string $commentaire = null): void
    {
        $fiche->update(['statut' => StatutFichePoste::ADOPTEE, 'adoptee_at' => now()]);
        $this->journaliser($fiche, 'adoption', $userId, $commentaire);
    }

    /** adoptée → brouillon (nouvelle version) pour révision. */
    public function reviser(FichePoste $fiche, ?int $userId = null, ?string $commentaire = null): void
    {
        $version = (string) (((int) ($fiche->version ?: 1)) + 1);
        $fiche->update(['statut' => StatutFichePoste::BROUILLON, 'version' => $version]);
        $this->journaliser($fiche, 'revision', $userId, $commentaire);
    }

    private function journaliser(FichePoste $fiche, string $etape, ?int $userId, ?string $commentaire): void
    {
        $fiche->validations()->create([
            'etape'       => $etape,
            'user_id'     => $userId,
            'version'     => $fiche->version,
            'commentaire' => $commentaire,
        ]);
    }
}
