<?php

namespace App\Console\Commands;

use App\Models\AgentIndemnite;
use App\Models\BaremeLogement;
use App\Models\Categorie;
use App\Models\Emploi;
use App\Models\Indemnite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Déduit le caractère ENSEIGNANT de chaque emploi à partir du montant de
 * logement (issu de liehoun) : ce montant encode (catégorie × enseignant ×
 * en-classe) via le barème logement. On remonte donc, par agent, le flag
 * enseignant, puis on l'agrège par emploi (vote majoritaire).
 *
 * Corrige les flags emploi.enseignant posés heuristiquement (cf. R8/R10 et la
 * résolution du barème logement).
 */
class DeriverEnseignantEmplois extends Command
{
    protected $signature = 'emplois:deriver-enseignant {--dry-run : Affiche sans rien modifier}';

    protected $description = 'Déduit emploi.enseignant à partir des montants de logement (liehoun) par catégorie.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Reverse barème logement : (catégorie|montant) → ensemble des valeurs enseignant.
        $rev = [];
        foreach (BaremeLogement::all() as $b) {
            $rev[strtoupper($b->categorie_code) . '|' . (int) round($b->montant)][(int) $b->enseignant] = true;
        }

        $logId = Indemnite::where('code', 'LOG')->value('id');
        if (! $logId) {
            $this->error('Indemnité LOG absente du catalogue (lancez d\'abord agents:importer-indemnites).');
            return self::FAILURE;
        }
        $logParAgent = AgentIndemnite::where('indemnite_id', $logId)->pluck('montant', 'agent_id');
        $catParId = Categorie::pluck('code', 'id')->map(fn ($c) => strtoupper($c));

        // Vote enseignant par emploi.
        $votes = []; // emploi_id => ['o'=>n, 'n'=>n]
        $indetermine = 0;
        DB::table('agents')->select('id', 'emploi_id', 'categorie_id')
            ->whereNotNull('emploi_id')->whereNotNull('categorie_id')
            ->orderBy('id')->chunk(5000, function ($agents) use (&$votes, &$indetermine, $rev, $logParAgent, $catParId) {
                foreach ($agents as $a) {
                    $log = $logParAgent[$a->id] ?? null;
                    $cat = $catParId[$a->categorie_id] ?? null;
                    if ($log === null || $cat === null) {
                        continue;
                    }
                    $cle = $cat . '|' . (int) round($log);
                    $vals = $rev[$cle] ?? null;
                    if (! $vals || count($vals) !== 1) {
                        $indetermine++;
                        continue; // montant ambigu (ex. P) ou inconnu
                    }
                    $ens = array_key_first($vals); // 0 ou 1
                    $votes[$a->emploi_id][$ens ? 'o' : 'n'] = ($votes[$a->emploi_id][$ens ? 'o' : 'n'] ?? 0) + 1;
                }
            });

        // Application par emploi (vote majoritaire).
        $changes = 0;
        $details = [];
        foreach (Emploi::all() as $e) {
            $v = $votes[$e->id] ?? null;
            if (! $v) {
                continue;
            }
            $cible = ($v['o'] ?? 0) > ($v['n'] ?? 0);
            if ((bool) $e->enseignant !== $cible) {
                $changes++;
                if (count($details) < 25) {
                    $details[] = sprintf('%s %s→%s (o:%d/n:%d) %s', $e->code, $e->enseignant ? 'O' : 'N', $cible ? 'O' : 'N', $v['o'] ?? 0, $v['n'] ?? 0, $e->libelle);
                }
                if (! $dry) {
                    $e->enseignant = $cible;
                    $e->saveQuietly();
                }
            }
        }

        $this->newLine();
        foreach ($details as $d) {
            $this->line('  ' . $d);
        }
        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ {$changes} emploi(s) corrigé(s) ; {$indetermine} agent(s) au montant ambigu/inconnu (ignorés).");

        return self::SUCCESS;
    }
}
