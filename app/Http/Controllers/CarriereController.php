<?php

namespace App\Http\Controllers;

use App\Enums\TypeEvenementCarriere;
use App\Http\Requests\StoreCarriereRequest;
use App\Models\Agent;
use App\Models\Categorie;
use App\Models\CarriereEvenement;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Fonction;
use App\Models\PositionAdministrative;
use App\Models\Poste;
use App\Services\CarriereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarriereController extends Controller
{
    public function __construct(private CarriereService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('carriere.view');

        $evenements = CarriereEvenement::with(['agent', 'createur'])
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->input('agent_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->latest('date_effet')
            ->paginate(20)
            ->withQueryString();

        return view('carriere.index', [
            'evenements' => $evenements,
            'types'      => TypeEvenementCarriere::options(),
            'filtres'    => $request->only(['agent_id', 'type']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('carriere.manage');

        return view('carriere.create', array_merge([
            'agents'   => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'types'    => TypeEvenementCarriere::options(),
            'agentSel' => $request->integer('agent'),
        ], $this->referentiels()));
    }

    public function store(StoreCarriereRequest $request): RedirectResponse
    {
        $agent = Agent::findOrFail($request->validated()['agent_id']);

        $this->service->enregistrer($agent, $request->validated(), $request->user()->id);

        return redirect()->route('agents.show', $agent)
            ->with('success', "Acte de carrière enregistré pour {$agent->nom_complet}.");
    }

    /** Nomenclatures de la grille et de l'emploi pour le formulaire. */
    private function referentiels(): array
    {
        return [
            'categories' => Categorie::orderBy('code')->pluck('code', 'id'),
            'echelles'   => Echelle::orderBy('libelle')->pluck('libelle', 'id'),
            'classes'    => Classe::orderBy('libelle')->pluck('libelle', 'id'),
            'echelons'   => Echelon::orderBy('rang')->pluck('libelle', 'id'),
            'fonctions'  => Fonction::orderBy('libelle')->pluck('libelle', 'id'),
            'postes'     => Poste::orderBy('libelle')->pluck('libelle', 'id'),
            'positions'  => PositionAdministrative::orderBy('libelle')->pluck('libelle', 'id'),
        ];
    }
}
