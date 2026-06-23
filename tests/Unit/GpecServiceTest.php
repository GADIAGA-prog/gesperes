<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\Emploi;
use App\Services\GpecService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GpecServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function projette_les_departs_et_besoins_par_emploi(): void
    {
        $emploi = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'enseignant' => true, 'actif' => true]);
        $an = now()->year + 1;

        foreach (['06-01', '09-01'] as $i => $jour) {
            Agent::create([
                'matricule' => '4000000' . $i, 'nom' => 'AG' . $i, 'prenoms' => 'X', 'sexe' => 'M',
                'emploi_id' => $emploi->id, 'date_retraite' => "{$an}-{$jour}",
            ]);
        }

        $service = new GpecService();

        $this->assertSame(2, $service->departsParAnnee(5)[$an]);
        $this->assertSame(2, $service->besoinsParEmploi(5)['Professeur']);
    }
}
