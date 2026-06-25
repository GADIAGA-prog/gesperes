<?php

namespace Tests\Feature;

use App\Enums\TypeStructure;
use App\Models\Agent;
use App\Models\Emploi;
use App\Models\Structure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recherche multicritère unique : un seul terme doit pouvoir matcher
 * matricule, nom, prénoms, emploi ou structure (y compris un parent de cascade).
 */
class AgentRechercheTest extends TestCase
{
    use RefreshDatabase;

    private function fixtures(): void
    {
        $prof = Emploi::create(['code' => 'PCL', 'libelle' => 'Professeur certifié des lycées', 'enseignant' => true]);
        $admin = Emploi::create(['code' => 'ATA', 'libelle' => 'Attaché d\'administration', 'enseignant' => false]);

        $direction = Structure::create(['code' => 'DR', 'libelle' => 'DRESFPT-Goulmou', 'type' => TypeStructure::DIRECTION->value]);
        $service = Structure::create(['code' => 'SV', 'libelle' => 'DPESFPT-Gourma', 'type' => TypeStructure::SERVICE->value, 'parent_id' => $direction->id]);

        Agent::create(['matricule' => '374203', 'nom' => 'ABADI', 'prenoms' => 'Yara', 'sexe' => 'F', 'emploi_id' => $prof->id, 'structure_id' => $service->id]);
        Agent::create(['matricule' => '999000', 'nom' => 'ZONGO', 'prenoms' => 'Paul', 'sexe' => 'M', 'emploi_id' => $admin->id]);
    }

    public function test_recherche_par_nom(): void
    {
        $this->fixtures();
        $this->assertSame(['ABADI'], Agent::recherche('abadi')->pluck('nom')->all());
    }

    public function test_recherche_par_emploi(): void
    {
        $this->fixtures();
        $this->assertSame(['ABADI'], Agent::recherche('professeur')->pluck('nom')->all());
    }

    public function test_recherche_par_structure_parent_de_cascade(): void
    {
        $this->fixtures();
        // « Goulmou » est la direction (parent) ; l'agent est sur le service enfant.
        $this->assertSame(['ABADI'], Agent::recherche('Goulmou')->pluck('nom')->all());
    }

    public function test_recherche_multi_mots_combine_les_criteres(): void
    {
        $this->fixtures();
        // « Gourma » (structure) + « Yara » (prénom) doivent désigner le même agent.
        $this->assertSame(['ABADI'], Agent::recherche('Gourma Yara')->pluck('nom')->all());
    }
}
