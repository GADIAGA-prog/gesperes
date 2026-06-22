<?php

namespace App\Http\Controllers;

use App\Enums\TypeStructure;
use App\Http\Requests\StoreStructureRequest;
use App\Http\Requests\UpdateStructureRequest;
use App\Models\Localite;
use App\Models\Structure;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StructureController extends Controller
{
    public function index(): View
    {
        $this->authorize('structures.view');

        $structures = Structure::with('parent')
            ->withCount('agents')
            ->orderBy('type')
            ->orderBy('libelle')
            ->get();

        // Construction de l'arborescence (racines = sans parent)
        $racines = $structures->whereNull('parent_id');

        return view('structures.index', [
            'structures' => $structures,
            'racines'    => $racines,
        ]);
    }

    public function create(): View
    {
        $this->authorize('structures.create');
        return view('structures.create', $this->referentiels());
    }

    public function store(StoreStructureRequest $request): RedirectResponse
    {
        $structure = Structure::create($request->validated());

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

        return [
            'parents'   => $parents,
            'localites' => Localite::orderBy('libelle')->pluck('libelle', 'id'),
            'types'     => collect(TypeStructure::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]),
        ];
    }
}
