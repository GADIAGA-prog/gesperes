<?php

namespace App\Http\Controllers;

use App\Enums\TypeStructure;
use App\Http\Requests\StoreStructureRequest;
use App\Http\Requests\UpdateStructureRequest;
use App\Models\Action;
use App\Models\Agent;
use App\Models\Localite;
use App\Models\Province;
use App\Models\Region;
use App\Models\Structure;
use App\Services\StructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StructureController extends Controller
{
    public function __construct(private StructureService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('structures.view');

        $structures = Structure::with('parent')
            ->withCount('agents')
            ->orderBy('type')
            ->orderBy('libelle')
            ->get();

        // Recherche : libellé, code ou responsable → liste plate avec le chemin complet.
        $q = trim((string) $request->input('q'));
        $resultats = $q === '' ? null : Structure::with(['parent.parent.parent.parent', 'responsable'])
            ->withCount('agents')
            ->where(fn ($w) => $w
                ->where('libelle', 'like', "%{$q}%")
                ->orWhere('code', 'like', "%{$q}%")
                ->orWhereHas('responsable', fn ($r) => $r->recherche($q)))
            ->orderBy('libelle')
            ->limit(100)
            ->get();

        return view('structures.index', [
            'structures' => $structures,
            'racines'    => $structures->whereNull('parent_id'), // arborescence (racines = sans parent)
            'resultats'  => $resultats,
            'q'          => $q,
        ]);
    }

    public function create(): View
    {
        $this->authorize('structures.create');
        return view('structures.create', $this->referentiels());
    }

    /** Recherche AJAX d'agents pour alimenter le sélecteur « responsable » (43k agents → pas de chargement complet). */
    public function rechercheAgents(Request $request): JsonResponse
    {
        $this->authorize('structures.view');

        $agents = Agent::query()
            ->recherche($request->input('q'))
            ->orderBy('nom')
            ->limit(20)
            ->get(['id', 'matricule', 'nom', 'prenoms']);

        return response()->json(
            $agents->map(fn (Agent $a) => [
                'id'   => $a->id,
                'text' => "{$a->matricule} — {$a->nom_complet}",
            ])->values()
        );
    }

    public function store(StoreStructureRequest $request): RedirectResponse
    {
        $structure = Structure::create($request->validated());
        $this->service->synchroniserResponsable($structure);

        return redirect()->route('structures.index')
            ->with('success', "Structure « {$structure->libelle} » créée.");
    }

    public function show(Structure $structure): View
    {
        $this->authorize('structures.view');
        $structure->load(['parent', 'enfants', 'localite', 'responsable']);
        $structure->loadCount('agents');

        return view('structures.show', ['structure' => $structure]);
    }

    public function edit(Structure $structure): View
    {
        $this->authorize('structures.update');
        return view('structures.edit', array_merge(['structure' => $structure], $this->referentiels($structure)));
    }

    public function update(UpdateStructureRequest $request, Structure $structure): RedirectResponse
    {
        $structure->update($request->validated());
        $this->service->synchroniserResponsable($structure);

        return redirect()->route('structures.index')
            ->with('success', "Structure « {$structure->libelle} » mise à jour.");
    }

    public function destroy(Structure $structure): RedirectResponse
    {
        $this->authorize('structures.delete');

        if ($structure->enfants()->exists()) {
            return back()->with('error', 'Impossible de supprimer une structure qui contient des sous-structures.');
        }
        if ($structure->agents()->exists()) {
            return back()->with('error', 'Impossible de supprimer une structure à laquelle des agents sont rattachés.');
        }

        $libelle = $structure->libelle;
        $structure->delete();

        return redirect()->route('structures.index')->with('success', "Structure « {$libelle} » supprimée.");
    }

    private function referentiels(?Structure $exclure = null): array
    {
        $parents = Structure::orderBy('libelle')
            ->when($exclure, fn ($query) => $query->where('id', '!=', $exclure->id))
            ->pluck('libelle', 'id');

        $provinces       = Province::orderBy('libelle')->get(['id', 'libelle', 'region_id']);
        $localitesProvin = Localite::whereNotNull('province_id')->orderBy('libelle')->get(['id', 'libelle', 'province_id']);

        return [
            'parents'   => $parents,
            'regions'   => Region::orderBy('libelle')->pluck('libelle', 'id'),
            'types'     => collect(TypeStructure::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]),
            'actions'   => Action::orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => $a->code . ' — ' . $a->libelle]),

            // Cascade géographique : Région → Province/Circonscription → Commune.
            'provincesParRegion'   => $provinces->groupBy('region_id')
                ->map(fn ($g) => $g->map(fn ($p) => ['id' => $p->id, 'libelle' => $p->libelle])->values()),
            'localitesParProvince' => $localitesProvin->groupBy('province_id')
                ->map(fn ($g) => $g->map(fn ($l) => ['id' => $l->id, 'libelle' => $l->libelle])->values()),
        ];
    }
}
