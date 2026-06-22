<?php

namespace Database\Factories;

use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use App\Enums\StatutDossier;
use App\Models\Categorie;
use App\Models\Emploi;
use App\Models\PositionAdministrative;
use App\Models\Structure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    public function definition(): array
    {
        $sexe = $this->faker->randomElement(Sexe::cases());

        return [
            'matricule' => $this->faker->unique()->numerify('########'),
            'cle'       => strtoupper($this->faker->randomLetter()),
            'nom'       => $this->faker->lastName(),
            'prenoms'   => $this->faker->firstName($sexe === Sexe::F ? 'female' : 'male'),
            'sexe'      => $sexe->value,
            'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'nationalite' => 'Burkinabè',
            'telephone'   => '22' . $this->faker->numerify('#######'),
            'email'       => $this->faker->optional()->safeEmail(),
            'emploi_id'   => Emploi::inRandomOrder()->value('id'),
            'categorie_id' => Categorie::inRandomOrder()->value('id'),
            'position_administrative_id' => PositionAdministrative::inRandomOrder()->value('id'),
            'structure_id' => Structure::inRandomOrder()->value('id'),
            'region'      => $this->faker->randomElement(['Centre', 'Hauts-Bassins', 'Centre-Ouest', 'Plateau-Central', 'Sahel', 'Boucle du Mouhoun', 'Nord', 'Est', 'Sud-Ouest', 'Centre-Nord', 'Cascades', 'Centre-Sud', 'Centre-Est']),
            'nombre_enfants' => $this->faker->numberBetween(0, 6),
            'situation_matrimoniale' => $this->faker->randomElement(SituationMatrimoniale::cases())->value,
            'date_integration' => $this->faker->optional()->dateTimeBetween('-30 years', '-1 year')?->format('Y-m-d'),
            'statut_dossier' => $this->faker->randomElement(StatutDossier::cases())->value,
            'allocation_familiale' => 0,
        ];
    }
}
