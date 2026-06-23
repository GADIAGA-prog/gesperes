<?php

namespace Tests\Unit;

use App\Enums\StatutSuiviDossier;
use App\Models\SuiviDossier;
use App\Services\SuiviDossierService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuiviDossierServiceTest extends TestCase
{
    private SuiviDossierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SuiviDossierService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Construit un dossier non persisté avec les attributs utiles au calcul. */
    private function dossier(array $attrs): SuiviDossier
    {
        return new SuiviDossier(array_merge([
            'reference_bordereau' => 'B-001',
            'structure_id'        => 1,
            'etape'               => 'reception',
            'statut'              => StatutSuiviDossier::EN_COURS->value,
            'date_reception'      => '2026-01-01',
            'delai_jours'         => 10,
        ], $attrs));
    }

    #[Test]
    public function date_limite_est_reception_plus_delai(): void
    {
        $d = $this->dossier(['date_reception' => '2026-01-01', 'delai_jours' => 10]);

        $this->assertEquals('2026-01-11', $this->service->dateLimite($d)->toDateString());
    }

    #[Test]
    public function date_limite_nulle_sans_delai(): void
    {
        $d = $this->dossier(['delai_jours' => 0]);

        $this->assertNull($this->service->dateLimite($d));
    }

    #[Test]
    public function dossier_en_cours_dans_les_temps_n_est_pas_en_retard(): void
    {
        Carbon::setTestNow('2026-01-05'); // échéance le 11 → encore dans les temps
        $d = $this->dossier(['date_reception' => '2026-01-01', 'delai_jours' => 10]);

        $this->assertFalse($this->service->estEnRetard($d));
        $this->assertSame(6, $this->service->joursRestants($d));
    }

    #[Test]
    public function dossier_en_cours_apres_echeance_est_en_retard(): void
    {
        Carbon::setTestNow('2026-01-20'); // échéance le 11 → dépassée de 9 jours
        $d = $this->dossier(['date_reception' => '2026-01-01', 'delai_jours' => 10]);

        $this->assertTrue($this->service->estEnRetard($d));
        $this->assertSame(-9, $this->service->joursRestants($d));
    }

    #[Test]
    public function dossier_traite_a_temps_n_est_pas_en_retard(): void
    {
        Carbon::setTestNow('2026-03-01'); // aujourd'hui bien après l'échéance...
        $d = $this->dossier([
            'date_reception'  => '2026-01-01',
            'delai_jours'     => 10,
            'statut'          => StatutSuiviDossier::TRAITE->value,
            'date_traitement' => '2026-01-08', // ...mais traité avant l'échéance
        ]);

        $this->assertFalse($this->service->estEnRetard($d));
    }

    #[Test]
    public function dossier_traite_en_retard_reste_en_retard(): void
    {
        Carbon::setTestNow('2026-03-01');
        $d = $this->dossier([
            'date_reception'  => '2026-01-01',
            'delai_jours'     => 10,
            'statut'          => StatutSuiviDossier::TRAITE->value,
            'date_traitement' => '2026-01-15', // traité 4 jours après l'échéance
        ]);

        $this->assertTrue($this->service->estEnRetard($d));
    }
}
