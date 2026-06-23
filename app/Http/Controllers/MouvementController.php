<?php

namespace App\Http\Controllers;

use App\Enums\CategoriePosition;
use App\Http\Requests\StoreMouvementRequest;
use App\Models\Agent;
use App\Models\Mouvement;
use App\Models\PositionAdministrative;
use App\Services\MouvementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MouvementController extends Controller
{
    public function __construct(private MouvementService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('mouvements.view');

        $mouvements = Mouvement::with(['agent', 'anciennePosition', 'nouvellePosition'])
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->input('agent_id')))
            ->when($request->filled('famille'), fn ($q) => $q->whereHas('nouvellePosition',
                fn ($p) => $p->where('categorie', $request->input('famille'))))
            ->latest('date_effet')
            ->paginate(20)
            ->withQueryString();

        return view('mouvements.index', [
            'mouvements' => $mouvements,
            'familles'   => CategoriePosition::options(),
            'filtres'    => $request->only(['agent_id', 'famille']),
        ]);
    }

    /**
     * Situation des sorties temporaires (disponibilité, détachement, mise à
     * disposition, suspension…) : agents actuellement hors activité de façon
     * temporaire, avec durée et alerte de fin proche.
     */
    public function sortiesTemporaires(Request $request): View
    {
        $this->authorize('mouvements.view');

        $page = $this->agentsParCategorie(CategoriePosition::SORTIE_TEMPORAIRE, $request);
        $this->service->decorerSortiesTemporaires($page);

        return view('mouvements.sorties-temporaires', [
            'agents'  => $page,
            'natures' => $this->naturesParCategorie(CategoriePosition::SORTIE_TEMPORAIRE),
            'filtres' => $request->only(['nature']),
        ]);
    }

    /**
     * Situation des sorties définitives (retraite, décès, démission,
     * licenciement…). Pas de date de reprise ; alerte sur les retraités de
     * l'année en cours.
     */
    public function sortiesDefinitives(Request $request): View
    {
        $this->authorize('mouvements.view');

        $page = $this->agentsParCategorie(CategoriePosition::SORTIE_DEFINITIVE, $request);
        $this->service->decorerSortiesDefinitives($page);

        return view('mouvements.sorties-definitives', [
            'agents'  => $page,
            'natures' => $this->naturesParCategorie(CategoriePosition::SORTIE_DEFINITIVE),
            'filtres' => $request->only(['nature']),
        ]);
    }

    /**
     * Agents dont la position administrative COURANTE appartient à la catégorie
     * donnée. Garantit qu'un agent ne figure que dans une seule situation
     * (activité / sortie temporaire / sortie définitive) à la fois.
     */
    private function agentsParCategorie(CategoriePosition $categorie, Request $request)
    {
        return Agent::query()
            ->whereHas('positionAdministrative', fn ($q) => $q->where('categorie', $categorie->value))
            ->when($request->filled('nature'),
                fn ($q) => $q->where('position_administrative_id', $request->input('nature')))
            ->with(['emploi', 'positionAdministrative', 'dernierMouvement'])
            ->orderBy('nom')->orderBy('prenoms')
            ->paginate(30)
            ->withQueryString();
    }

    /** Natures (positions actives) disponibles pour une catégorie de sortie. */
    private function naturesParCategorie(CategoriePosition $categorie)
    {
        return PositionAdministrative::where('categorie', $categorie->value)
            ->where('actif', true)
            ->orderBy('libelle')
            ->pluck('libelle', 'id');
    }

    public function create(Request $request): View
    {
        $this->authorize('mouvements.manage');

        $positions = PositionAdministrative::where('actif', true)->orderBy('libelle')->get();

        return view('mouvements.create', [
            'agents'    => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'positions' => $positions->mapWithKeys(fn ($p) => [$p->id => $p->categorie?->label() . ' — ' . $p->libelle]),
            'agentSel'  => $request->integer('agent'),
        ]);
    }

    public function store(StoreMouvementRequest $request): RedirectResponse
    {
        $agent = Agent::findOrFail($request->validated()['agent_id']);

        $this->service->enregistrer($agent, $request->validated(), $request->user()->id);

        return redirect()->route('agents.show', $agent)
            ->with('success', "Mouvement enregistré pour {$agent->nom_complet}.");
    }
}
