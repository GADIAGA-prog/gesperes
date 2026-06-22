<?php

namespace Tests\Unit;

use App\Services\RetraiteService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RetraiteServiceTest extends TestCase
{
    private RetraiteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RetraiteService();
    }

    #[Test]
    public function age_legal_defaut_est_60(): void
    {
        $this->assertSame(60, $this->service->ageLegal(null));
        $this->assertSame(60, $this->service->ageLegal('INCONNUE'));
    }

    #[Test]
    public function date_retraite_calculee_correctement(): void
    {
        // Agent né le 01/01/1980, âge légal = 60 → retraite le 01/01/2040
        $naissance = Carbon::parse('1980-01-01');
        $date = $this->service->dateRetraite($naissance, null);

        $this->assertNotNull($date);
        $this->assertEquals('2040-01-01', $date->toDateString());
    }

    #[Test]
    public function date_retraite_nulle_si_naissance_absente(): void
    {
        $date = $this->service->dateRetraite(null, null);
        $this->assertNull($date);
    }

    #[Test]
    public function age_legal_lit_config(): void
    {
        // Si aucune config de catégorie, retourne l'âge par défaut de la config
        config(['gesperes.retraite.age_defaut' => 63]);
        $this->assertSame(63, $this->service->ageLegal(null));
        // Remise à 60 pour ne pas polluer les autres tests
        config(['gesperes.retraite.age_defaut' => 60]);
    }
}
