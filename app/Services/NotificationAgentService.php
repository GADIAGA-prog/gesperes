<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Document;
use App\Models\NotificationRh;
use Illuminate\Support\Str;

/**
 * Génère les notifications destinées à l'espace agent (self-service) lorsqu'un
 * acte ou un évènement concerne un agent disposant d'un compte personnel.
 *
 * Réutilise la table notifications_rh (clé `cle` unique => anti-doublon), comme
 * la tâche planifiée des alertes RH.
 */
class NotificationAgentService
{
    /**
     * Notifie l'agent qu'un nouvel acte (document) a été versé à son dossier.
     * Ne fait rien si l'agent n'a pas de compte rattaché.
     */
    public function notifierNouvelActe(Document $document): void
    {
        $agent = $document->agent;
        if (! $agent || ! $agent->user_id) {
            return;
        }

        $libelle = $document->type_document?->label() ?? 'Document';

        $this->creer(
            agent: $agent,
            type: 'acte',
            cle: 'acte-' . $document->id,
            titre: 'Nouvel acte dans votre dossier',
            message: $libelle . ($document->reference ? " — réf. {$document->reference}" : '') . ' a été ajouté à votre dossier.',
            niveau: 'info',
        );
    }

    /** Crée une notification idempotente (ignore les doublons sur `cle`). */
    public function creer(Agent $agent, string $type, string $cle, string $titre, string $message, string $niveau = 'info'): void
    {
        NotificationRh::firstOrCreate(
            ['cle' => $cle],
            [
                'type'     => $type,
                'agent_id' => $agent->id,
                'titre'    => $titre,
                'message'  => Str::limit($message, 480),
                'niveau'   => $niveau,
                'lu'       => false,
            ]
        );
    }
}
