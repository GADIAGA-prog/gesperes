<?php

namespace Tests\Feature;

use App\Enums\StatutFichePoste;
use App\Enums\TypePoste;
use App\Models\FichePoste;
use App\Services\FichePosteWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FichePosteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function fiche(): FichePoste
    {
        return FichePoste::create([
            'intitule'   => 'Chargé d\'analyse',
            'type_poste' => TypePoste::OPERATIONNEL->value,
            'statut'     => StatutFichePoste::BROUILLON->value,
        ]);
    }

    public function test_le_workflow_passe_de_brouillon_a_adoptee(): void
    {
        $service = app(FichePosteWorkflowService::class);
        $fiche = $this->fiche();

        $this->assertTrue($fiche->peutSoumettre());
        $service->soumettre($fiche, null);
        $this->assertSame(StatutFichePoste::VALIDEE_SUPERIEUR, $fiche->fresh()->statut);

        $this->assertTrue($fiche->fresh()->peutAdopter());
        $service->adopter($fiche->fresh(), null);
        $fiche->refresh();
        $this->assertSame(StatutFichePoste::ADOPTEE, $fiche->statut);
        $this->assertNotNull($fiche->adoptee_at);
        $this->assertCount(2, $fiche->validations);
    }

    public function test_la_revision_incremente_la_version_et_repasse_en_brouillon(): void
    {
        $service = app(FichePosteWorkflowService::class);
        $fiche = $this->fiche();
        $service->soumettre($fiche, null);
        $service->adopter($fiche->fresh(), null);

        $service->reviser($fiche->fresh(), null);
        $fiche->refresh();

        $this->assertSame(StatutFichePoste::BROUILLON, $fiche->statut);
        $this->assertSame('2', $fiche->version);
        $this->assertSame('revision', $fiche->validations()->first()->etape);
    }
}
