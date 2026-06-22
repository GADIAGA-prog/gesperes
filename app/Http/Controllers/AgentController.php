<?php

namespace App\Http\Controllers;

use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use App\Enums\StatutDossier;
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
            ->with(['emploi', 'structure', 'positionAdministrative'])
            ->recherche($request->input('q'))
            ->region($request->input('region'))
            ->when($request->filled('statut_dossier'), fn ($query) =>
                $query->where('statut_dossier', $request->input('statut_dossier')))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $regions = Agent::query()->whereNotNull('region')->distinct()->orderBy('region')->pluck('region');

        return view('agents.index', [
            'agents'   => $agents,
            'regions'  => $regions,
            'statuts'  => StatutDossier::cases(),
            'filtres'  => $request->only(['q', 'region', 'statut_dossier']),
        ]);
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
            'specialite', 'documents', 'affectations.nouvelleStructure',
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
        return [
            'emplois'     => Emploi::orderBy('libelle')->get(),
            'fonctions'   => Fonction::orderBy('libelle')->pluck('libelle', 'id'),
            'postes'      => Poste::orderBy('libelle')->pluck('libelle', 'id'),
            'categories'  => Categorie::orderBy('code')->pluck('code', 'id'),
            'echelles'    => Echelle::orderBy('libelle')->pluck('libelle', 'id'),
            'classes'     => Classe::orderBy('libelle')->pluck('libelle', 'id'),
            'echelons'    => Echelon::orderBy('rang')->pluck('libelle', 'id'),
            'indices'     => Indice::orderBy('valeur')->pluck('valeur', 'id'),
            'positions'   => PositionAdministrative::orderBy('libelle')->pluck('libelle', 'id'),
            'structures'  => Structure::orderBy('libelle')->pluck('libelle', 'id'),
            'localites'   => Localite::orderBy('libelle')->pluck('libelle', 'id'),
            'typesEns'    => TypeEnseignement::orderBy('libelle')->pluck('libelle', 'id'),
            'specialites' => Specialite::orderBy('libelle')->pluck('libelle', 'id'),
            'sexes'       => collect(Sexe::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]),
            'situations'  => collect(SituationMatrimoniale::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]),
        ];
    }
}
