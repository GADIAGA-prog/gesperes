<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\AgentIndemnite;
use App\Models\Indemnite;
use App\Models\Indice;
use App\Services\PaiePersonnelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaiePersonnelServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function compose_la_ligne_de_paie_grille_plus_indemnites(): void
    {
        $indice = Indice::create(['code' => 'A111', 'valeur' => 638, 'libelle' => 'Test', 'actif' => true]);
        $fonction = \App\Models\Fonction::create(['code' => 'DIR', 'libelle' => 'Directeur', 'indemnite_responsabilite' => 10500, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => 'T1', 'nom' => 'TEST', 'prenoms' => 'Agent', 'sexe' => 'M',
            'indice_id' => $indice->id, 'fonction_id' => $fonction->id,
        ]);

        foreach ([['LOG', 69300], ['ASTR', 30500]] as [$code, $montant]) {
            $i = Indemnite::create(['code' => $code, 'libelle' => $code, 'mode' => 'montant_fixe', 'valeur' => 0, 'actif' => true]);
            AgentIndemnite::create(['agent_id' => $agent->id, 'indemnite_id' => $i->id, 'montant' => $montant, 'actif' => true]);
        }

        $agent->load(['indice', 'fonction', 'indemnites.indemnite']);
        $ligne = app(PaiePersonnelService::class)->ligne($agent);

        // Grille (indice 638) — cf. GrilleIndiciaireServiceTest.
        $this->assertSame(123932.0, $ligne['solde']);
        $this->assertSame(12393.0, $ligne['residence']);
        $this->assertSame(16731.0, $ligne['carfo']);

        // Indemnités réelles.
        $this->assertSame(69300.0, $ligne['logement']);
        $this->assertSame(30500.0, $ligne['astreinte']);
        $this->assertSame(10500.0, $ligne['responsabilite']);
        $this->assertSame(0.0, $ligne['specifique']);

        // Total mensuel = solde + résidence + indemnités (CARFO hors total).
        $this->assertSame(123932.0 + 12393.0 + 69300.0 + 30500.0 + 10500.0, $ligne['total_mois']);
        $this->assertSame($ligne['total_mois'] * 12, $ligne['total_annuel']);
    }
}
