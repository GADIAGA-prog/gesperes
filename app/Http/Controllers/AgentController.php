<?php

namespace App\Http\Controllers;

use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use App\Exports\AgentsExport;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Emploi;
use App\Models\Fonction;
use App\Models\Indice;
use App\Models\Localite;
use App\Models\Poste;
use App\Models\PositionAdministrative;
use App\Models\Province;
use App\Models\Region;
use App\Models\Specialite;
use App\Models\Structure;
use App\Models\TypeEnseignement;
use App\Services\AgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function __construct(private AgentService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('agents.view');

        $agents = Agent::query()
            ->with(['emploi', 'structure.parent.parent.parent.parent', 'positionAdministrative'])
            ->recherche($request->input('q'))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $donnees = [
            'agents'   => $agents,
            'filtres'  => $request->only(['q']),
            'colonnesExport' => AgentsExport::colonnesDisponibles(),
        ];

        // Recherche en direct : on ne renvoie que le tableau de résultats (sans le layout).
        if ($request->ajax()) {
            return view('agents._resultats', $donnees);
        }

        return view('agents.index', $donnees);
    }

    public function create(): View
    {
        $this->authorize('agents.create');
        return view('agents.create', $this->referentiels());
    }

    public function store(StoreAgentRequest $request): RedirectResponse
    {
        $agent = $this->service->creer($request->validated(), $request->user()->id);

        return redirect()->route('agents.show', $agent)
            ->with('success', "Agent {$agent->nom_complet} créé avec succès.");
    }

    public function show(Agent $agent): View
    {
        $this->authorize('agents.view');

        $agent->load([
            'emploi', 'fonction', 'poste', 'categorie', 'echelle', 'classe', 'echelon',
            'indice', 'positionAdministrative', 'structure', 'localite', 'typeEnseignement',
            'specialite', 'documents', 'affectations.nouvelleStructure', 'evenementsCarriere',
            'mouvements.anciennePosition', 'mouvements.nouvellePosition',
            'dossiersDisciplinaires', 'competences', 'evaluations',
        ]);

        return view('agents.show', ['agent' => $agent]);
    }

    public function edit(Agent $agent): View
    {
        $this->authorize('agents.update');
        return view('agents.edit', array_merge(['agent' => $agent], $this->referentiels()));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $this->service->mettreAJour($agent, $request->validated());

        return redirect()->route('agents.show', $agent)
            ->with('success', "Agent {$agent->nom_complet} mis à jour.");
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $this->authorize('agents.delete');
        $nom = $agent->nom_complet;
        $agent->delete();

        return redirect()->route('agents.index')
            ->with('success', "Agent {$nom} supprimé.");
    }

    /** Données de référence partagées par les formulaires create/edit. */
    private function referentiels(): array
    {
        $emplois  = Emploi::orderBy('libelle')->get();
        $echelles = Echelle::orderBy('libelle')->get();
        $indices  = Indice::orderBy('valeur')->get();

        $provinces       = Province::orderBy('libelle')->get(['id', 'libelle', 'region_id']);
        $localitesProvin = Localite::whereNotNull('province_id')->orderBy('libelle')->get(['id', 'libelle', 'province_id']);

        // Arborescence des structures pour la cascade hiérarchique (parent → … → service/poste).
        $structuresArbre = Structure::orderBy('libelle')->get(['id', 'libelle', 'parent_id']);
        $parentsAvecEnfants = $structuresArbre->pluck('parent_id')->filter()->unique()->flip();

        return [
            'emplois'     => $emplois,
            'fonctions'   => Fonction::orderBy('libelle')->pluck('libelle', 'id'),
            'postes'      => Poste::orderBy('libelle')->pluck('libelle', 'id'),
            'categories'  => Categorie::orderBy('code')->pluck('code', 'id'),
            'echelles'    => $echelles->pluck('libelle', 'id'),
            'classes'     => Classe::orderBy('libelle')->pluck('libelle', 'id'),
            'echelons'    => Echelon::orderBy('rang')->pluck('libelle', 'id'),
            'indices'     => $indices->pluck('valeur', 'id'),
            'positions'   => PositionAdministrative::orderBy('libelle')->pluck('libelle', 'id'),
            'structures'  => Structure::orderBy('libelle')->pluck('libelle', 'id'),
            // Fiches de poste adoptées, rattachables à l'agent (titulaire).
            'fichesPoste' => \App\Models\FichePoste::where('statut', \App\Enums\StatutFichePoste::ADOPTEE->value)
                ->orderBy('intitule')->get()
                ->mapWithKeys(fn ($f) => [$f->id => ($f->code ? $f->code . ' — ' : '') . $f->intitule]),
            // Cascade hiérarchique des structures (JS) : enfants par parent + carte des parents.
            'structuresCascade' => [
                'enfants' => $structuresArbre
                    ->groupBy(fn ($s) => $s->parent_id ? (string) $s->parent_id : 'racine')
                    ->map(fn ($grp) => $grp->map(fn ($s) => [
                        'id'      => $s->id,
                        'libelle' => $s->libelle,
                        'feuille' => ! $parentsAvecEnfants->has($s->id),
                    ])->values()),
                'parents' => $structuresArbre->mapWithKeys(fn ($s) => [$s->id => $s->parent_id]),
            ],
            'etablissements' => \App\Models\Agent::whereNotNull('etablissement')->where('etablissement', '!=', '')
                ->distinct()->orderBy('etablissement')->pluck('etablissement'),
            'regions'     => Region::orderBy('libelle')->pluck('libelle', 'id'),
            'typesEns'    => TypeEnseignement::orderBy('libelle')->pluck('libelle', 'id'),
            'specialites' => Specialite::orderBy('libelle')->pluck('libelle', 'id'),
            'sexes'       => collect(Sexe::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]),
            'situations'  => collect(SituationMatrimoniale::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]),

            // --- Cartes d'auto-remplissage de la grille indiciaire (JS) ---
            // Emploi → catégorie
            'emploiCategorie'   => $emplois->whereNotNull('categorie_id')->pluck('categorie_id', 'id'),
            // Catégorie → échelles disponibles, dérivées de la grille des indices
            // (les échelles ne portent pas de categorie_id ; la liaison vit dans la table indices).
            // Auto-sélection de l'échelle uniquement si la catégorie n'en a qu'une.
            'categorieEchelles' => $indices->whereNotNull('categorie_id')->whereNotNull('echelle_id')
                ->groupBy('categorie_id')->map(fn ($g) => $g->pluck('echelle_id')->unique()->values()),
            // Quadruplet « categorie-echelle-classe-echelon » → indice
            'indiceGrille'      => $indices->filter(fn ($i) =>
                    $i->categorie_id && $i->echelle_id && $i->classe_id && $i->echelon_id)
                ->mapWithKeys(fn ($i) => [
                    "{$i->categorie_id}-{$i->echelle_id}-{$i->classe_id}-{$i->echelon_id}" => $i->id,
                ]),

            // --- Cascade géographique (JS) : Région → Province/Circonscription → Commune ---
            'provincesParRegion'   => $provinces->groupBy('region_id')
                ->map(fn ($g) => $g->map(fn ($p) => ['id' => $p->id, 'libelle' => $p->libelle])->values()),
            'localitesParProvince' => $localitesProvin->groupBy('province_id')
                ->map(fn ($g) => $g->map(fn ($l) => ['id' => $l->id, 'libelle' => $l->libelle])->values()),
        ];
    }
}
