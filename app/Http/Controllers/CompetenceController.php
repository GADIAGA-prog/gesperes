<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Competence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetenceController extends Controller
{
    private const NIVEAUX = ['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé', 'expert' => 'Expert'];

    /* ───────── Référentiel des compétences ───────── */

    public function index(): View
    {
        $this->authorize('competences.view');

        return view('competences.index', [
            'competences' => Competence::orderBy('libelle')->withCount('agents')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('competences.manage');
        $data = $request->validate([
            'code'    => ['required', 'string', 'max:50', Rule::unique('competences', 'code')],
            'libelle' => ['required', 'string', 'max:160'],
            'domaine' => ['nullable', 'string', 'max:120'],
        ]);
        Competence::create($data + ['actif' => true]);

        return back()->with('success', 'Compétence ajoutée.');
    }

    public function update(Request $request, Competence $competence): RedirectResponse
    {
        $this->authorize('competences.manage');
        $data = $request->validate([
            'code'    => ['required', 'string', 'max:50', Rule::unique('competences', 'code')->ignore($competence->id)],
            'libelle' => ['required', 'string', 'max:160'],
            'domaine' => ['nullable', 'string', 'max:120'],
            'actif'   => ['nullable', 'boolean'],
        ]);
        $competence->update($data + ['actif' => $request->boolean('actif')]);

        return back()->with('success', 'Compétence mise à jour.');
    }

    public function destroy(Competence $competence): RedirectResponse
    {
        $this->authorize('competences.manage');
        $competence->delete();

        return back()->with('success', 'Compétence supprimée.');
    }

    /* ───────── Compétences d'un agent ───────── */

    public function agent(Agent $agent): View
    {
        $this->authorize('competences.view');

        return view('competences.agent', [
            'agent'       => $agent->load('competences'),
            'disponibles' => Competence::where('actif', true)->orderBy('libelle')->get()
                ->whereNotIn('id', $agent->competences->pluck('id')),
            'niveaux'     => self::NIVEAUX,
        ]);
    }

    public function attribuer(Request $request, Agent $agent): RedirectResponse
    {
        $this->authorize('competences.manage');
        $data = $request->validate([
            'competence_id'    => ['required', 'exists:competences,id'],
            'niveau'           => ['required', Rule::in(array_keys(self::NIVEAUX))],
            'date_acquisition' => ['nullable', 'date'],
            'source'           => ['nullable', 'in:formation,experience'],
        ]);

        $agent->competences()->syncWithoutDetaching([
            $data['competence_id'] => [
                'niveau'           => $data['niveau'],
                'date_acquisition' => $data['date_acquisition'] ?? null,
                'source'           => $data['source'] ?? null,
            ],
        ]);

        return back()->with('success', 'Compétence attribuée.');
    }

    public function retirer(Agent $agent, Competence $competence): RedirectResponse
    {
        $this->authorize('competences.manage');
        $agent->competences()->detach($competence->id);

        return back()->with('success', 'Compétence retirée.');
    }
}
