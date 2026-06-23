<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Evaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('performance.view');

        $evaluations = Evaluation::with(['agent', 'evaluateur'])
            ->when($request->filled('periode'), fn ($q) => $q->where('periode', $request->input('periode')))
            ->latest('date_evaluation')
            ->paginate(20)
            ->withQueryString();

        return view('performance.index', [
            'evaluations' => $evaluations,
            'periodes'    => Evaluation::distinct()->orderByDesc('periode')->pluck('periode'),
            'filtres'     => $request->only('periode'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('performance.manage');

        return view('performance.create', [
            'agents'   => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'agentSel' => $request->integer('agent'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('performance.manage');
        $data = $this->valider($request);

        Evaluation::create($data + ['evaluateur_id' => $request->user()->id, 'created_by' => $request->user()->id]);

        return redirect()->route('agents.show', $data['agent_id'])->with('success', 'Évaluation enregistrée.');
    }

    public function edit(Evaluation $performance): View
    {
        $this->authorize('performance.manage');

        return view('performance.edit', ['evaluation' => $performance->load('agent')]);
    }

    public function update(Request $request, Evaluation $performance): RedirectResponse
    {
        $this->authorize('performance.manage');
        $performance->update($this->valider($request, $performance->id));

        return redirect()->route('performance.index')->with('success', 'Évaluation mise à jour.');
    }

    public function destroy(Evaluation $performance): RedirectResponse
    {
        $this->authorize('performance.manage');
        $performance->delete();

        return back()->with('success', 'Évaluation supprimée.');
    }

    private function valider(Request $request, ?int $ignore = null): array
    {
        return $request->validate([
            'agent_id'        => ['required', 'exists:agents,id'],
            'periode'         => ['required', 'integer', 'between:2000,2100'],
            'date_evaluation' => ['required', 'date'],
            'note'            => ['nullable', 'numeric', 'min:0', 'max:20'],
            'objectifs'       => ['nullable', 'string'],
            'appreciation'    => ['nullable', 'string'],
            'statut'          => ['required', 'in:brouillon,valide'],
        ]);
    }
}
