<?php

namespace Database\Seeders;

use App\Models\MppProcedure;
use App\Models\NatureDossier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Natures de dossier pour le module Suivi des dossiers, dérivées du référentiel
 * MPP GRH (Manuel des Processus et Procédures de la GRH).
 *
 * Chaque procédure MPP des processus ciblés devient une nature de dossier ;
 * son délai par défaut est la somme des délais d'étapes (opérations) exprimés en
 * jours (les durées de tâche en minutes/heures sont ignorées car ce sont des
 * temps d'exécution, pas des délais de traitement de dossier).
 *
 * La liste se complète ensuite manuellement via l'écran « Natures », et se
 * resynchronise sans doublon si on relance le seeder (clé : mpp_procedure_id).
 */
class NatureDossierSeeder extends Seeder
{
    /**
     * Processus MPP dont les procédures génèrent des dossiers/actes à suivre.
     * (comparaison insensible aux accents/casse)
     */
    private array $processusCibles = [
        'Gestion des carrieres',
        'Gestion de la mobilite',
    ];

    public function run(): void
    {
        $procedures = MppProcedure::with(['processus', 'operations'])->get();

        if ($procedures->isEmpty()) {
            $this->command->warn('⚠ Référentiel MPP GRH absent : importez-le (php artisan gesperes:importer-mpp) puis relancez ce seeder. Aucune nature créée.');
            return;
        }

        // Nettoyage des 5 natures provisoires non rattachées au MPP et sans dossier.
        NatureDossier::whereIn('code', ['DETACH', 'DISPO', 'AVANC', 'TITUL', 'RECLAS'])
            ->whereNull('mpp_procedure_id')
            ->whereDoesntHave('dossiers')
            ->delete();

        $ciblesNorm = array_map([$this, 'normaliser'], $this->processusCibles);
        $compteur = 0;

        foreach ($procedures as $proc) {
            $processusLib = $this->normaliser($proc->processus?->libelle ?? '');
            if (! in_array($processusLib, $ciblesNorm, true)) {
                continue;
            }

            $delai = $this->delaiTotalJours($proc);

            NatureDossier::updateOrCreate(
                ['mpp_procedure_id' => $proc->id],
                [
                    'code'               => $this->extraireCode($proc->libelle),
                    'libelle'            => $this->nettoyerLibelle($proc->libelle),
                    'delai_defaut_jours' => $delai > 0 ? $delai : null,
                    'actif'              => true,
                ]
            );
            $compteur++;
        }

        $this->command->info("✓ {$compteur} natures de dossier dérivées du référentiel MPP GRH.");
    }

    /** Somme des délais des opérations d'une procédure, convertis en jours. */
    private function delaiTotalJours(MppProcedure $proc): int
    {
        return (int) $proc->operations->reduce(
            fn (int $total, $op) => $total + $this->delaiEnJours((string) $op->delais),
            0
        );
    }

    /**
     * Convertit un texte de délai en jours en additionnant chaque « nombre + unité »
     * rencontré. Les unités inférieures au jour (minutes, heures) sont ignorées.
     */
    private function delaiEnJours(string $texte): int
    {
        $texte = Str::lower($texte);
        if ($texte === '') {
            return 0;
        }

        preg_match_all(
            '/(\d+)\s*(mn|min|minutes?|heures?|h|jours?|j|semaines?|mois|années?|ans?|an)/u',
            $texte,
            $matches,
            PREG_SET_ORDER
        );

        $jours = 0;
        foreach ($matches as $m) {
            $n = (int) $m[1];
            $jours += match (true) {
                str_starts_with($m[2], 'semaine') => $n * 7,
                str_starts_with($m[2], 'mois')    => $n * 30,
                str_starts_with($m[2], 'an'), str_starts_with($m[2], 'ann') => $n * 365,
                str_starts_with($m[2], 'jour'), $m[2] === 'j' => $n,
                default => 0, // mn / min / h / heures → durée de tâche, ignorée
            };
        }

        return $jours;
    }

    /** Extrait la numérotation MPP comme code (ex : « 12.4. Sorties… » → « 12.4 »). */
    private function extraireCode(string $libelle): ?string
    {
        return preg_match('/^\s*(\d+(?:\.\d+)*)/u', $libelle, $m) ? $m[1] : null;
    }

    /** Retire la numérotation et la ponctuation de tête, normalise les espaces. */
    private function nettoyerLibelle(string $libelle): string
    {
        $clean = preg_replace('/^\s*\d+(?:\.\d+)*\s*[\.\-:)]*\s*/u', '', $libelle);
        $clean = preg_replace('/\s+/u', ' ', (string) $clean);

        return Str::ucfirst(trim($clean));
    }

    /** Minuscule sans accents pour comparer des libellés de processus. */
    private function normaliser(string $valeur): string
    {
        return Str::lower(Str::ascii(trim($valeur)));
    }
}
