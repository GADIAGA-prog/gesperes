<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTpeeRequest;
use App\Models\Emploi;
use App\Models\MppProcessus;
use App\Models\PrevisionEffectif;
use App\Models\Structure;
use App\Services\TpeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Module « Outils GRH » : sous-modules transverses de gestion prévisionnelle et
 * qualitative des RH (en complément de la GPEC déjà existante).
 */
class OutilsGrhController extends Controller
{
    /** Horizon du TPEE (années). */
    private const HORIZON = 3;

    public function tpee(Request $request, TpeeService $service): View
    {
        $this->authorize('gpec.view');

        $structureId = $request->integer('structure_id') ?: null;
        $q = $request->input('q');

        return view('outils-grh.tpee', [
            'tableau'    => $service->tableau(self::HORIZON, $structureId, $q),
            'structures' => Structure::directions()->orderBy('libelle')->pluck('libelle', 'id'),
            'filtres'    => ['structure_id' => $structureId, 'q' => $q],
        ]);
    }

    /** Enregistre les hypothèses (entrées prévues, effectif cible) du TPEE. */
    public function tpeeStore(StoreTpeeRequest $request): RedirectResponse
    {
        $structureId = $request->integer('structure_id') ?: null;
        $lignes = $request->input('lignes', []);

        // Emplois réellement existants (les clés viennent du formulaire).
        $emploisValides = Emploi::whereIn('id', array_keys($lignes))->pluck('id')->flip();
        $anneesValides = range((int) now()->year, (int) now()->year + self::HORIZON - 1);

        foreach ($lignes as $emploiId => $parAnnee) {
            if (! $emploisValides->has((int) $emploiId)) {
                continue;
            }
            foreach ($parAnnee as $annee => $vals) {
                if (! in_array((int) $annee, $anneesValides, true)) {
                    continue;
                }

                $entrees = (int) ($vals['entrees'] ?? 0);
                $cible = ($vals['cible'] ?? null) === '' ? null : $vals['cible'];
                $cible = $cible === null ? null : (int) $cible;

                $cle = ['emploi_id' => (int) $emploiId, 'annee' => (int) $annee, 'structure_id' => $structureId];

                // Ligne vide : on retire l'éventuelle prévision existante.
                if ($entrees === 0 && $cible === null) {
                    PrevisionEffectif::where($cle)->delete();
                    continue;
                }

                PrevisionEffectif::updateOrCreate($cle, [
                    'entrees_prevues' => $entrees,
                    'effectif_cible'  => $cible,
                    'created_by'      => $request->user()->id,
                ]);
            }
        }

        return redirect()
            ->route('outils-grh.tpee', array_filter(['structure_id' => $structureId, 'q' => $request->input('q')]))
            ->with('success', 'Prévisions du TPEE enregistrées.');
    }

    /** Export PDF du tableau prévisionnel (A4 paysage). */
    public function tpeePdf(Request $request, TpeeService $service): Response
    {
        $this->authorize('gpec.view');

        $structureId = $request->integer('structure_id') ?: null;
        $structure = $structureId ? Structure::find($structureId) : null;

        $pdf = Pdf::loadView('outils-grh.tpee-pdf', [
            'tableau'   => $service->tableau(self::HORIZON, $structureId, $request->input('q')),
            'structure' => $structure,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('TPEE_' . ($structure?->code ?? 'national') . '_' . now()->format('Ymd') . '.pdf');
    }

    public function referentielsMpp(Request $request): View
    {
        $this->authorize('gpec.view');

        $processus = MppProcessus::withCount('procedures')->orderBy('ordre')->get();
        $selectionId = $request->integer('processus') ?: $processus->first()?->id;
        $selection = $selectionId
            ? MppProcessus::with('procedures.operations')->find($selectionId)
            : null;

        return view('outils-grh.referentiels-mpp', [
            'processus'  => $processus,
            'selection'  => $selection,
        ]);
    }
}
