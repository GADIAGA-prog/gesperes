<?php

namespace App\Http\Controllers;

use App\Models\MppProcessus;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Module « Outils GRH » : sous-modules transverses de gestion prévisionnelle et
 * qualitative des RH (en complément de la GPEC déjà existante).
 */
class OutilsGrhController extends Controller
{
    public function fichesPoste(): View
    {
        $this->authorize('gpec.view');

        return view('outils-grh.fiches-poste');
    }

    public function tpee(): View
    {
        $this->authorize('gpec.view');

        return view('outils-grh.tpee');
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

    public function planFormation(): View
    {
        $this->authorize('gpec.view');

        return view('outils-grh.plan-formation');
    }
}
