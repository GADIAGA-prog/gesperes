<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\BaremeTechnicite;
use App\Models\Echelle;
use App\Models\Indemnite;
use App\Models\Indice;
use App\Services\IndemniteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndemniteServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function calcule_montant_fixe_et_pourcentage(): void
    {
        config(['grille.point_annuel' => 2331, 'grille.mois_par_an' => 12]);

        // Salaire indiciaire = 300 × 2331 / 12 = 58 275
        $indice = Indice::create(['code' => 'IDX300', 'valeur' => 300, 'actif' => true]);
        $agent = Agent::create([
            'matricule' => '30000001', 'nom' => 'KABORE', 'prenoms' => 'Paul', 'sexe' => 'M',
            'indice_id' => $indice->id,
        ]);

        $fixe = Indemnite::create(['code' => 'LOG', 'libelle' => 'Logement', 'mode' => 'montant_fixe', 'valeur' => 20000, 'actif' => true]);
        $pct  = Indemnite::create(['code' => 'SUJ', 'libelle' => 'Sujétion', 'mode' => 'pourcentage', 'valeur' => 10, 'actif' => true]);

        $service = new IndemniteService();

        $this->assertSame(20000.0, $service->calculer($agent, $fixe));
        $this->assertEqualsWithDelta(5827.5, $service->calculer($agent, $pct), 0.01);
    }

    #[Test]
    public function calcule_un_bareme_de_technicite_selon_l_echelle(): void
    {
        $echelle = Echelle::create(['code' => 'A1', 'libelle' => 'A1', 'actif' => true]);
        BaremeTechnicite::create(['echelle_code' => 'A1', 'montant' => 27000, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => '30000002', 'nom' => 'OUEDRAOGO', 'prenoms' => 'Awa', 'sexe' => 'F',
            'echelle_id' => $echelle->id,
        ]);

        $tech = Indemnite::create(['code' => 'TECH', 'libelle' => 'Technicité', 'mode' => 'bareme', 'bareme' => 'technicite', 'valeur' => 0, 'actif' => true]);

        $this->assertSame(27000.0, (new IndemniteService())->calculer($agent, $tech));
    }
}
