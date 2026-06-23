<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Indice;
use App\Services\CarriereService;
use App\Services\RetraiteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarriereServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CarriereService
    {
        return new CarriereService(new RetraiteService());
    }

    #[Test]
    public function avancement_echelon_met_a_jour_agent_et_recalcule_indice(): void
    {
        $cat = Categorie::create(['code' => 'B', 'libelle' => 'Catégorie B', 'actif' => true]);
        $ech = Echelle::create(['code' => 'E1', 'libelle' => 'Échelle 1', 'categorie_id' => $cat->id, 'actif' => true]);
        $cls = Classe::create(['code' => 'C1', 'libelle' => 'Classe 1', 'actif' => true]);
        $e1 = Echelon::create(['code' => 'EC1', 'libelle' => '1er échelon', 'rang' => 1, 'actif' => true]);
        $e2 = Echelon::create(['code' => 'EC2', 'libelle' => '2e échelon', 'rang' => 2, 'actif' => true]);
        $i1 = Indice::create(['code' => 'I1', 'valeur' => 300, 'categorie_id' => $cat->id, 'echelle_id' => $ech->id, 'classe_id' => $cls->id, 'echelon_id' => $e1->id, 'actif' => true]);
        $i2 = Indice::create(['code' => 'I2', 'valeur' => 350, 'categorie_id' => $cat->id, 'echelle_id' => $ech->id, 'classe_id' => $cls->id, 'echelon_id' => $e2->id, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => '10000001', 'nom' => 'DOE', 'prenoms' => 'John', 'sexe' => 'M',
            'date_naissance' => '1980-01-01',
            'categorie_id' => $cat->id, 'echelle_id' => $ech->id, 'classe_id' => $cls->id,
            'echelon_id' => $e1->id, 'indice_id' => $i1->id,
        ]);

        $evt = $this->service()->enregistrer($agent, [
            'type'              => 'avancement_echelon',
            'date_effet'        => '2026-01-01',
            'nouvel_echelon_id' => $e2->id,
        ], null);

        $agent->refresh();
        $this->assertSame($e2->id, $agent->echelon_id);
        $this->assertSame($i2->id, $agent->indice_id, "L'indice doit être recalculé depuis la grille.");
        $this->assertSame($e1->id, $evt->ancien_echelon_id);
        $this->assertSame($i2->id, $evt->nouvel_indice_id);
        $this->assertStringContainsString('Échelon', $evt->description);
    }

    #[Test]
    public function promotion_categorie_recalcule_la_date_de_retraite(): void
    {
        config(['gesperes.retraite.par_categorie' => ['A' => 63]]);

        $catB = Categorie::create(['code' => 'B', 'libelle' => 'B', 'actif' => true]);
        $catA = Categorie::create(['code' => 'A', 'libelle' => 'A', 'actif' => true]);

        $agent = Agent::create([
            'matricule' => '10000002', 'nom' => 'ROE', 'prenoms' => 'Jane', 'sexe' => 'F',
            'date_naissance' => '1980-01-01', 'categorie_id' => $catB->id,
        ]);

        $this->service()->enregistrer($agent, [
            'type'                  => 'promotion',
            'date_effet'            => '2026-01-01',
            'nouvelle_categorie_id' => $catA->id,
        ], null);

        $agent->refresh();
        $this->assertSame($catA->id, $agent->categorie_id);
        // 1980 + 63 ans (âge légal catégorie A) = 2043
        $this->assertSame('2043-01-01', $agent->date_retraite?->toDateString());
    }
}
