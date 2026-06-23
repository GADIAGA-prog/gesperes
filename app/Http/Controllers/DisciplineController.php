<?php

namespace App\Http\Controllers;

use App\Enums\TypeDiscipline;
use App\Http\Requests\StoreDossierDisciplinaireRequest;
use App\Models\Agent;
use App\Models\DossierDisciplinaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('discipline.view');

        $dossiers = DossierDisciplinaire::with('agent')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->latest('date_acte')
            ->paginate(20)
            ->withQueryString();

        return view('discipline.index', [
            'dossiers' => $dossiers,
            'types'    => TypeDiscipline::options(),
            'filtres'  => $request->only(['type', 'statut']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('discipline.manage');

        return view('discipline.create', [
            'agents'   => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'types'    => TypeDiscipline::options(),
            'agentSel' => $request->integer('agent'),
        ]);
    }

    public function store(StoreDossierDisciplinaireRequest $request): RedirectResponse
    {
        $dossier = DossierDisciplinaire::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('agents.show', $dossier->agent_id)
            ->with('success', 'Acte disciplinaire enregistré.');
    }

    public function edit(DossierDisciplinaire $discipline): View
    {
        $this->authorize('discipline.manage');

        return view('discipline.edit', [
            'dossier' => $discipline->load('agent'),
            'types'   => TypeDiscipline::options(),
        ]);
    }

    public function update(StoreDossierDisciplinaireRequest $request, DossierDisciplinaire $discipline): RedirectResponse
    {
        $discipline->update($request->validated());

        return redirect()->route('discipline.index')->with('success', 'Acte disciplinaire mis à jour.');
    }

    public function destroy(DossierDisciplinaire $discipline): RedirectResponse
    {
        $this->authorize('discipline.manage');
        $discipline->delete();

        return back()->with('success', 'Acte disciplinaire supprimé.');
    }
}
