<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\Emploi;
use App\Models\PrevisionEffectif;
use App\Services\TpeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TpeeServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function projette_effectif_departs_entrees_et_ecart_par_emploi(): void
    {
        $y = (int) now()->year;
        $emploi = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'actif' => true]);

        // 3 agents : 1 départ en Y, 1 départ en Y+1, 1 sans retraite proche.
        Agent::create(['matricule' => 'A1', 'nom' => 'A', 'prenoms' => 'x', 'sexe' => 'M', 'emploi_id' => $emploi->id, 'date_retraite' => "{$y}-09-01"]);
        Agent::create(['matricule' => 'A2', 'nom' => 'B', 'prenoms' => 'x', 'sexe' => 'M', 'emploi_id' => $emploi->id, 'date_retraite' => ($y + 1) . '-03-01']);
        Agent::create(['matricule' => 'A3', 'nom' => 'C', 'prenoms' => 'x', 'sexe' => 'F', 'emploi_id' => $emploi->id, 'date_retraite' => ($y + 9) . '-01-01']);

        // Hypothèses pour Y : 5 entrées, cible 10 (national).
        PrevisionEffectif::create(['emploi_id' => $emploi->id, 'annee' => $y, 'entrees_prevues' => 5, 'effectif_cible' => 10]);

        $tableau = app(TpeeService::class)->tableau(3);
        $ligne = collect($tableau['lignes'])->firstWhere('emploi.id', $emploi->id);

        $this->assertSame(3, $ligne['effectif']);

        // Y : début 3 − 1 départ + 5 entrées = 7 ; écart = 10 − 7 = 3.
        $this->assertSame(1, $ligne['annees'][$y]['dep']);
        $this->assertSame(5, $ligne['annees'][$y]['ent']);
        $this->assertSame(7, $ligne['annees'][$y]['fin']);
        $this->assertSame(3, $ligne['annees'][$y]['ecart']);

        // Y+1 : début 7 (report) − 1 départ = 6 ; pas de cible → écart null.
        $this->assertSame(1, $ligne['annees'][$y + 1]['dep']);
        $this->assertSame(6, $ligne['annees'][$y + 1]['fin']);
        $this->assertNull($ligne['annees'][$y + 1]['ecart']);

        // Y+2 : début 6, aucun départ → 6.
        $this->assertSame(6, $ligne['annees'][$y + 2]['fin']);
    }
}
