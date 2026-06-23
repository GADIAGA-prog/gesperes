<?php

namespace App\Console\Commands;

use App\Models\Fonction;
use App\Models\Indemnite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Déduit l'indemnité de responsabilité (indemnité de fonction) de chaque
 * fonction à partir des montants RESP observés (liehoun) : on retient le
 * montant le plus fréquent (mode) parmi les agents de la fonction, à condition
 * qu'il concerne au moins {seuil} agents (sinon 0, cas des montants isolés).
 */
class DeriverResponsabiliteFonctions extends Command
{
    protected $signature = 'fonctions:deriver-responsabilite {--dry-run} {--seuil=3}';

    protected $description = 'Renseigne fonction.indemnite_responsabilite (mode des montants RESP par fonction).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $seuil = (int) $this->option('seuil');

        $respId = Indemnite::where('code', 'RESP')->value('id');
        if (! $respId) {
            $this->error('Indemnité RESP absente.');
            return self::FAILURE;
        }

        $rows = DB::table('agent_indemnites as ai')
            ->join('agents as ag', 'ag.id', '=', 'ai.agent_id')
            ->where('ai.indemnite_id', $respId)
            ->whereNotNull('ag.fonction_id')
            ->groupBy('ag.fonction_id', 'ai.montant')
            ->selectRaw('ag.fonction_id, ai.montant, COUNT(*) n')
            ->get()
            ->groupBy('fonction_id');

        $maj = 0;
        foreach (Fonction::all() as $f) {
            // « Agent » = fonction par défaut (non-encadrement) → pas d'indemnité de fonction.
            $estAgent = trim(mb_strtolower($f->libelle)) === 'agent';

            $candidats = collect($rows->get($f->id, []))->sortByDesc('n')->values();
            $mode = $candidats->first();
            $montant = (! $estAgent && $mode && $mode->n >= $seuil) ? (int) round($mode->montant) : 0;

            if ((int) $f->indemnite_responsabilite !== $montant) {
                $maj++;
                $this->line(sprintf('  %-22s %8s  (mode %d agents)', $f->libelle, number_format($montant, 0, '', ' '), $mode->n ?? 0));
                if (! $dry) {
                    $f->indemnite_responsabilite = $montant;
                    $f->saveQuietly();
                }
            }
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ {$maj} fonction(s) mise(s) à jour.");

        return self::SUCCESS;
    }
}
