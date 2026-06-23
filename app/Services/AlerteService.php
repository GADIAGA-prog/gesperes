<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Document;
use App\Models\NotificationRh;

/**
 * Génère les notifications RH persistantes à partir de l'état courant des données :
 * agents proches de la retraite, documents expirés ou bientôt expirés.
 * Idempotent : la clé logique évite les doublons.
 */
class AlerteService
{
    public function generer(): int
    {
        return $this->retraites() + $this->documents();
    }

    private function retraites(): int
    {
        $mois = (int) config('gesperes.retraite.alerte_mois_avant', 24);
        $n = 0;

        $agents = Agent::whereNotNull('date_retraite')
            ->whereDate('date_retraite', '>=', now())
            ->whereDate('date_retraite', '<=', now()->addMonths($mois))
            ->get();

        foreach ($agents as $agent) {
            $n += $this->creer(
                'retraite',
                'retraite-' . $agent->id . '-' . $agent->date_retraite->format('Ym'),
                $agent->id,
                'Départ à la retraite proche',
                "{$agent->nom_complet} part à la retraite le {$agent->date_retraite->format('d/m/Y')}.",
                'warning',
            );
        }

        return $n;
    }

    private function documents(): int
    {
        $n = 0;

        $expires = Document::with('agent')->where('archive', false)->expires()->get();
        foreach ($expires as $doc) {
            $n += $this->creer(
                'document_expire',
                'docexp-' . $doc->id,
                $doc->agent_id,
                'Document expiré',
                "{$doc->type_document?->label()} de {$doc->agent?->nom_complet} expiré le {$doc->date_expiration?->format('d/m/Y')}.",
                'danger',
            );
        }

        $bientot = Document::with('agent')->where('archive', false)
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>=', now())
            ->whereDate('date_expiration', '<=', now()->addDays(60))
            ->get();
        foreach ($bientot as $doc) {
            $n += $this->creer(
                'document_bientot',
                'docsoon-' . $doc->id,
                $doc->agent_id,
                'Document bientôt expiré',
                "{$doc->type_document?->label()} de {$doc->agent?->nom_complet} expire le {$doc->date_expiration?->format('d/m/Y')}.",
                'warning',
            );
        }

        return $n;
    }

    private function creer(string $type, string $cle, ?int $agentId, string $titre, string $message, string $niveau): int
    {
        $notif = NotificationRh::firstOrCreate(
            ['cle' => $cle],
            ['type' => $type, 'agent_id' => $agentId, 'titre' => $titre, 'message' => $message, 'niveau' => $niveau, 'lu' => false],
        );

        return $notif->wasRecentlyCreated ? 1 : 0;
    }
}
