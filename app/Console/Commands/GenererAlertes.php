<?php

namespace App\Console\Commands;

use App\Mail\DigestAlertesRh;
use App\Models\NotificationRh;
use App\Services\AlerteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenererAlertes extends Command
{
    protected $signature = 'alertes:generer';

    protected $description = 'Génère les notifications RH (retraites proches, documents expirés/bientôt expirés) et envoie le digest e-mail si configuré.';

    public function handle(AlerteService $service): int
    {
        $n = $service->generer();
        $this->info("✓ {$n} nouvelle(s) alerte(s) RH générée(s).");

        $destinataires = (array) config('gesperes.alertes.email_destinataires', []);
        if (! empty($destinataires)) {
            $parType = NotificationRh::nonLues()->selectRaw('type, COUNT(*) c')->groupBy('type')->pluck('c', 'type');

            Mail::to($destinataires)->send(new DigestAlertesRh(
                (int) ($parType['retraite'] ?? 0),
                (int) ($parType['document_expire'] ?? 0),
                (int) ($parType['document_bientot'] ?? 0),
                NotificationRh::nonLues()->latest()->limit(50)->get(),
            ));

            $this->info('✉ Digest envoyé à ' . count($destinataires) . ' destinataire(s).');
        }

        return self::SUCCESS;
    }
}
