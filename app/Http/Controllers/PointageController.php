<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\MotifAbsence;
use App\Models\Pointage;
use App\Models\Structure;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Saisie de la situation journalière de présence des agents (Fiche A),
 * par structure et par date.
 */
class PointageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('pointage.view');

        $structureId = $request->integer('structure_id') ?: null;
        $date = $request->filled('date') ? $request->date('date') : Carbon::today();

        $agents = collect();
        $pointages = collect();
        $structure = null;

        if ($structureId) {
            $structure = Structure::find($structureId);
            $agents = Agent::where('structure_id', $structureId)
                ->with(['emploi', 'fonction'])
                ->orderBy('nom')->orderBy('prenoms')
                ->get();

            $pointages = Pointage::where('structure_id', $structureId)
                ->whereDate('date_pointage', $date)
                ->get()
                ->keyBy('agent_id');
        }

        return view('pointages.index', [
            'structures' => Structure::where('actif', true)->orderBy('libelle')->pluck('libelle', 'id'),
            'structureId' => $structureId,
            'structure'  => $structure,
            'date'       => $date->format('Y-m-d'),
            'agents'     => $agents,
            'pointages'  => $pointages,
            'motifs'     => MotifAbsence::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('pointage.manage');

        $data = $request->validate([
            'structure_id'          => ['required', 'exists:structures,id'],
            'date'                  => ['required', 'date'],
            'lignes'                => ['required', 'array'],
            'lignes.*.present'      => ['nullable', 'boolean'],
            'lignes.*.motif_absence_id' => ['nullable', 'exists:motifs_absence,id'],
            'lignes.*.duree_jours'  => ['nullable', 'numeric', 'min:0', 'max:1'],
            'lignes.*.duree_heures' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'lignes.*.reference_piece' => ['nullable', 'string', 'max:255'],
            'lignes.*.mesure_prise' => ['nullable', 'string', 'max:255'],
        ]);

        $enregistres = 0;

        foreach ($data['lignes'] as $agentId => $ligne) {
            // L'agent doit appartenir à la structure pointée.
            if (! Agent::where('id', $agentId)->where('structure_id', $data['structure_id'])->exists()) {
                continue;
            }

            $present = (bool) ($ligne['present'] ?? false);

            Pointage::updateOrCreate(
                ['agent_id' => $agentId, 'date_pointage' => $data['date']],
                [
                    'structure_id'     => $data['structure_id'],
                    'present'          => $present,
                    'motif_absence_id' => $present ? null : ($ligne['motif_absence_id'] ?? null),
                    'duree_jours'      => $present ? 0 : ($ligne['duree_jours'] ?? 0),
                    'duree_heures'     => $present ? 0 : ($ligne['duree_heures'] ?? 0),
                    'reference_piece'  => $present ? null : ($ligne['reference_piece'] ?? null),
                    'mesure_prise'     => $present ? null : ($ligne['mesure_prise'] ?? null),
                    'saisi_par'        => $request->user()->id,
                ]
            );
            $enregistres++;
        }

        return redirect()->route('pointages.index', [
            'structure_id' => $data['structure_id'],
            'date' => $data['date'],
        ])->with('success', "Pointage enregistré pour {$enregistres} agent(s).");
    }
}
