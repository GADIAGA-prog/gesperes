<?php

namespace Tests\Unit;

use App\Services\PlanFormationService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanFormationServiceTest extends TestCase
{
    private PlanFormationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlanFormationService();
    }

    #[Test]
    public function pourcentage_borne_et_gere_le_total_nul(): void
    {
        $this->assertSame(0.0, $this->service->pourcentage(5, 0));
        $this->assertSame(50.0, $this->service->pourcentage(5, 10));
        $this->assertSame(100.0, $this->service->pourcentage(15, 10)); // borné à 100
    }

    #[Test]
    public function taux_realisation_compare_agents_prevus_et_realises(): void
    {
        $this->assertSame(0.0, $this->service->tauxRealisation(20, 0));
        $this->assertSame(75.0, $this->service->tauxRealisation(20, 15));
        $this->assertSame(100.0, $this->service->tauxRealisation(20, 20));
    }

    #[Test]
    public function ecart_budget_calcule_difference_et_taux(): void
    {
        $r = $this->service->ecartBudget(1_000_000, 1_200_000);

        $this->assertSame(200000.0, $r['ecart']);
        $this->assertSame(100.0, $r['taux']); // consommation bornée à 100 %

        $r2 = $this->service->ecartBudget(1_000_000, 600_000);
        $this->assertSame(-400000.0, $r2['ecart']);
        $this->assertSame(60.0, $r2['taux']);
    }

    #[Test]
    public function totaux_actions_somme_cout_jours_agents(): void
    {
        $actions = new Collection([
            ['cout' => 11_395_000, 'nombre_jours' => 5, 'nombre_agents' => 25],
            ['cout' => 11_661_500, 'nombre_jours' => 5, 'nombre_agents' => 25],
        ]);

        $totaux = $this->service->totauxActions($actions);

        $this->assertSame(23056500.0, $totaux['cout']);
        $this->assertSame(10, $totaux['jours']);
        $this->assertSame(50, $totaux['agents']);
        $this->assertSame(2, $totaux['nombre']);
    }
}
