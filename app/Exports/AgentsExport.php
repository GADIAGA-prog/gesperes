<?php

namespace App\Exports;

use App\Models\Agent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AgentsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array  $filtres   Filtres de l'index (q, region, statut_dossier).
     * @param  array  $colonnes  Clés des colonnes à exporter (vide = toutes).
     */
    public function __construct(
        private array $filtres = [],
        private array $colonnes = [],
    ) {
        // On ne garde que des clés connues, dans l'ordre du catalogue.
        $valides = array_keys(static::catalogue());
        $this->colonnes = array_values(array_intersect($valides, $this->colonnes));
        if (empty($this->colonnes)) {
            $this->colonnes = $valides; // par défaut : toutes les colonnes
        }
    }

    /**
     * Catalogue complet des colonnes exportables.
     * Clé interne => ['label' => libellé Excel, 'valeur' => fn(Agent) => valeur].
     */
    public static function catalogue(): array
    {
        $colonnes = [
            'matricule'        => ['label' => 'Matricule', 'valeur' => fn (Agent $a) => $a->matricule],
            'cle'              => ['label' => 'Clé', 'valeur' => fn (Agent $a) => $a->cle],
            'nom'              => ['label' => 'Nom', 'valeur' => fn (Agent $a) => $a->nom],
            'prenoms'          => ['label' => 'Prénoms', 'valeur' => fn (Agent $a) => $a->prenoms],
            'sexe'             => ['label' => 'Sexe', 'valeur' => fn (Agent $a) => $a->sexe?->label()],
            'date_naissance'   => ['label' => 'Date de naissance', 'valeur' => fn (Agent $a) => $a->date_naissance?->format('d/m/Y')],
            'age'              => ['label' => 'Âge', 'valeur' => fn (Agent $a) => $a->age],
            'nationalite'      => ['label' => 'Nationalité', 'valeur' => fn (Agent $a) => $a->nationalite],
            'telephone'        => ['label' => 'Téléphone', 'valeur' => fn (Agent $a) => $a->telephone],
            'email'            => ['label' => 'E-mail', 'valeur' => fn (Agent $a) => $a->email],
            'adresse'          => ['label' => 'Adresse', 'valeur' => fn (Agent $a) => $a->adresse],

            'emploi'           => ['label' => 'Emploi', 'valeur' => fn (Agent $a) => $a->emploi?->libelle],
            'fonction'         => ['label' => 'Fonction', 'valeur' => fn (Agent $a) => $a->fonction?->libelle],
            'poste'            => ['label' => 'Poste', 'valeur' => fn (Agent $a) => $a->poste?->libelle],
            'categorie'        => ['label' => 'Catégorie', 'valeur' => fn (Agent $a) => $a->categorie?->code],
            'echelle'          => ['label' => 'Échelle', 'valeur' => fn (Agent $a) => $a->echelle?->libelle],
            'classe'           => ['label' => 'Classe', 'valeur' => fn (Agent $a) => $a->classe?->libelle],
            'echelon'          => ['label' => 'Échelon', 'valeur' => fn (Agent $a) => $a->echelon?->libelle],
            'indice'           => ['label' => 'Indice', 'valeur' => fn (Agent $a) => $a->indice?->valeur],
            'position'         => ['label' => 'Position administrative', 'valeur' => fn (Agent $a) => $a->positionAdministrative?->libelle],
            'date_integration' => ['label' => 'Date intégration', 'valeur' => fn (Agent $a) => $a->date_integration?->format('d/m/Y')],
            'date_nomination'  => ['label' => 'Date nomination', 'valeur' => fn (Agent $a) => $a->date_nomination?->format('d/m/Y')],
            'date_retraite'    => ['label' => 'Date retraite', 'valeur' => fn (Agent $a) => $a->date_retraite?->format('d/m/Y')],

            'structure'        => ['label' => 'Structure (unité)', 'valeur' => fn (Agent $a) => $a->structure?->niveauStructure()],
            'service'          => ['label' => 'Service', 'valeur' => fn (Agent $a) => $a->structure?->niveauService()],
            'rattachement_complet' => ['label' => 'Rattachement (chemin complet)', 'valeur' => fn (Agent $a) => $a->structure?->cheminComplet()],
            'region'           => ['label' => 'Région', 'valeur' => fn (Agent $a) => $a->region],
            'province'         => ['label' => 'Province', 'valeur' => fn (Agent $a) => $a->province],
            'commune'          => ['label' => 'Commune', 'valeur' => fn (Agent $a) => $a->commune],
            'etablissement'    => ['label' => 'Établissement', 'valeur' => fn (Agent $a) => $a->etablissement],
            'localite'         => ['label' => 'Localité', 'valeur' => fn (Agent $a) => $a->localite?->libelle],
            'date_affectation' => ['label' => 'Date affectation', 'valeur' => fn (Agent $a) => $a->date_affectation?->format('d/m/Y')],

            'type_enseignement' => ['label' => 'Type enseignement', 'valeur' => fn (Agent $a) => $a->typeEnseignement?->libelle],
            'specialite'        => ['label' => 'Spécialité', 'valeur' => fn (Agent $a) => $a->specialite?->libelle],
            'lieu_exercice'     => ['label' => "Lieu d'exercice", 'valeur' => fn (Agent $a) => $a->lieu_exercice?->label()],
            'volume_horaire_du' => ['label' => 'Volume horaire dû', 'valeur' => fn (Agent $a) => $a->volume_horaire_du],
            'volume_horaire_assure' => ['label' => 'Volume horaire assuré', 'valeur' => fn (Agent $a) => $a->volume_horaire_assure],

            'situation_matrimoniale' => ['label' => 'Situation matrimoniale', 'valeur' => fn (Agent $a) => $a->situation_matrimoniale?->label()],
            'nombre_enfants'    => ['label' => "Nombre d'enfants", 'valeur' => fn (Agent $a) => $a->nombre_enfants],
            'personnes_a_charge' => ['label' => 'Personnes à charge', 'valeur' => fn (Agent $a) => $a->personnes_a_charge],
            'allocation_familiale' => ['label' => 'Allocation familiale', 'valeur' => fn (Agent $a) => $a->allocation_familiale],

            'distinction_honorifique' => ['label' => 'Distinction honorifique', 'valeur' => fn (Agent $a) => $a->distinction_honorifique],
            'statut_dossier'   => ['label' => 'Statut dossier', 'valeur' => fn (Agent $a) => $a->statut_dossier?->label()],
            'observations'     => ['label' => 'Observations', 'valeur' => fn (Agent $a) => $a->observations],
        ];

        // Niveaux de rattachement éclatés (Niveau 1 = racine … Niveau N = service).
        $profondeur = \App\Models\Structure::profondeurMax();
        for ($i = 1; $i <= $profondeur; $i++) {
            $idx = $i - 1;
            $colonnes["niveau_{$i}"] = [
                'label'  => "Niveau {$i}",
                'valeur' => fn (Agent $a) => $a->structure?->cheminNiveaux()[$idx] ?? null,
            ];
        }

        return $colonnes;
    }

    /** Liste clé => libellé pour l'interface de sélection des colonnes. */
    public static function colonnesDisponibles(): array
    {
        return array_map(fn ($c) => $c['label'], static::catalogue());
    }

    public function query()
    {
        return Agent::query()
            ->with([
                'emploi', 'fonction', 'poste', 'categorie', 'echelle', 'classe', 'echelon',
                'indice', 'positionAdministrative', 'structure.parent.parent.parent.parent.parent',
                'localite', 'typeEnseignement', 'specialite',
            ])
            ->recherche($this->filtres['q'] ?? null)
            ->region($this->filtres['region'] ?? null)
            ->when(! empty($this->filtres['statut_dossier']), fn ($query) =>
                $query->where('statut_dossier', $this->filtres['statut_dossier']))
            ->orderBy('nom');
    }

    public function headings(): array
    {
        $catalogue = static::catalogue();

        return array_map(fn ($cle) => $catalogue[$cle]['label'], $this->colonnes);
    }

    public function map($agent): array
    {
        $catalogue = static::catalogue();

        return array_map(fn ($cle) => $catalogue[$cle]['valeur']($agent), $this->colonnes);
    }
}
