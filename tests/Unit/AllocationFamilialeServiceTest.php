<?php

namespace Tests\Unit;

use App\Services\AllocationFamilialeService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AllocationFamilialeServiceTest extends TestCase
{
    private AllocationFamilialeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AllocationFamilialeService();
    }

    #[Test]
    public function calcul_zero_enfant(): void
    {
        config(['gesperes.allocation_familiale.montant_par_enfant' => 2000, 'gesperes.allocation_familiale.nombre_max_enfants' => 6]);
        $this->assertEquals(0, $this->service->calculer(0));
    }

    #[Test]
    public function calcul_trois_enfants(): void
    {
        config(['gesperes.allocation_familiale.montant_par_enfant' => 2000, 'gesperes.allocation_familiale.nombre_max_enfants' => 6]);
        $this->assertEquals(6000, $this->service->calculer(3));
    }

    #[Test]
    public function calcul_plafonne_au_maximum(): void
    {
        config(['gesperes.allocation_familiale.montant_par_enfant' => 2000, 'gesperes.allocation_familiale.nombre_max_enfants' => 6]);
        // 10 enfants déclarés → plafonné à 6 × 2000 = 12 000
        $this->assertEquals(12000, $this->service->calculer(10));
    }
}
