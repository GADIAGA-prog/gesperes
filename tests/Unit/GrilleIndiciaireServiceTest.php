<?php

namespace Tests\Unit;

use App\Services\GrilleIndiciaireService;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GrilleIndiciaireServiceTest extends TestCase
{
    private GrilleIndiciaireService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GrilleIndiciaireService();
    }

    #[Test]
    public function calcule_les_elements_derives_de_l_indice(): void
    {
        // Indice 638 (catégorie A, échelle 1, classe 1, échelon 1), point 2331.
        $d = $this->service->detail(638);

        $this->assertSame(638 * 2331.0, $d['brut_annuel']);
        $this->assertSame(123932.0, $d['solde_indiciaire']);   // round(638×2331/12)
        $this->assertSame(16731.0, $d['carfo']);                // round(solde × 0,135)
        $this->assertSame(107201.0, $d['net_mensuel']);         // solde − carfo
        $this->assertEqualsWithDelta(12393.2, $d['residence'], 0.01); // solde / 10
        $this->assertSame(94800.0, $d['base_imposable']);       // tronc(net+résid−0,2×solde,100)
        $this->assertSame(8914.0, $d['iuts']);                  // tranche > 80 000
    }

    #[Test]
    public function l_iuts_diminue_avec_les_personnes_a_charge(): void
    {
        $sans = $this->service->iuts(638, 0);
        $avec = $this->service->iuts(638, 3);

        $this->assertSame(8914.0, $sans);
        $this->assertSame(round(8914 * 0.88), $avec); // facteur 3 charges = 0,88
        $this->assertLessThan($sans, $avec);
    }

    #[Test]
    public function le_nombre_de_charges_est_borne(): void
    {
        // Au-delà de 7 charges, on reste au dernier facteur (0,80) sans erreur.
        $this->assertSame($this->service->iuts(638, 7), $this->service->iuts(638, 99));
    }
}
