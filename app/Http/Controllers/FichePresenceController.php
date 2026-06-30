<?php

namespace App\Http\Controllers;

use App\Exports\FicheExcelExport;
use App\Models\Structure;
use App\Services\FichePresenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class FichePresenceController extends Controller
{
    public function __construct(private FichePresenceService $service) {}

    public function index(): View
    {
        $this->authorize('presence.reports');

        return view('fiches.index', [
            'structures' => Structure::where('actif', true)->orderBy('libelle')->pluck('libelle', 'id'),
            'annee'      => (int) now()->year,
            'mois'       => (int) now()->month,
            'trimestre'  => (int) ceil(now()->month / 3),
            'date'       => now()->format('Y-m-d'),
        ]);
    }

    public function ficheA(Request $request): Response
    {
        $this->authorize('presence.reports');
        $data = $request->validate([
            'structure_id' => ['required', 'exists:structures,id'],
            'date'         => ['required', 'date'],
            'format'       => ['nullable', 'in:pdf,xlsx'],
        ]);

        $fiche = $this->service->ficheA((int) $data['structure_id'], $data['date']);
        $nom = 'fiche_A_' . $data['date'];

        if (($data['format'] ?? 'pdf') === 'xlsx') {
            $entetes = ['N°', 'Nom et Prénoms', 'Matricule', 'Emploi', 'Fonction', 'Présent', 'Absent', 'Absence (Heure)', 'Absence (Jour)'];
            $lignes = array_map(fn ($l) => [
                $l['n'], $l['nom'], $l['matricule'], $l['emploi'], $l['fonction'],
                $l['present'] === true ? 'Oui' : '', $l['absent'] ? 'X' : '', $l['duree_heures'], $l['duree_jours'],
            ], $fiche['lignes']);

            return Excel::download(new FicheExcelExport('Fiche A', $entetes, $lignes), "{$nom}.xlsx");
        }

        return Pdf::loadView('fiches.pdf.fiche-a', $fiche)->setPaper('a4', 'landscape')->download("{$nom}.pdf");
    }

    public function ficheB(Request $request): Response
    {
        $this->authorize('presence.reports');
        $data = $request->validate([
            'structure_id' => ['required', 'exists:structures,id'],
            'mois'         => ['required', 'integer', 'between:1,12'],
            'annee'        => ['required', 'integer', 'between:2000,2100'],
            'format'       => ['nullable', 'in:pdf,xlsx'],
        ]);

        $fiche = $this->service->ficheB((int) $data['structure_id'], (int) $data['mois'], (int) $data['annee']);
        $nom = "fiche_B_{$data['annee']}_{$data['mois']}";

        if (($data['format'] ?? 'pdf') === 'xlsx') {
            $entetes = ['N°', 'Nom et Prénoms', 'Matricule', 'Emploi', 'Fonction', 'Absence (Heure)', 'Absence (Jour)', 'Mesures prises', 'Référence pièces justificatives'];
            $lignes = array_map(fn ($l) => [
                $l['n'], $l['nom'], $l['matricule'], $l['emploi'], $l['fonction'],
                $l['total_heures'], $l['total_jours'], $l['mesures'], $l['references'],
            ], $fiche['lignes']);

            return Excel::download(new FicheExcelExport('Fiche B', $entetes, $lignes), "{$nom}.xlsx");
        }

        return Pdf::loadView('fiches.pdf.fiche-b', $fiche)->setPaper('a4', 'landscape')->download("{$nom}.pdf");
    }

    public function ficheC(Request $request): Response
    {
        $this->authorize('presence.reports');
        $data = $request->validate([
            'mois'   => ['required', 'integer', 'between:1,12'],
            'annee'  => ['required', 'integer', 'between:2000,2100'],
            'format' => ['nullable', 'in:pdf,xlsx'],
        ]);

        $fiche = $this->service->ficheC((int) $data['mois'], (int) $data['annee']);
        $nom = "fiche_C_{$data['annee']}_{$data['mois']}";

        if (($data['format'] ?? 'pdf') === 'xlsx') {
            return Excel::download(new FicheExcelExport('Fiche C', $this->entetesRecap(), $this->lignesRecap($fiche['lignes'])), "{$nom}.xlsx");
        }

        return Pdf::loadView('fiches.pdf.fiche-c', $fiche)->setPaper('a4', 'landscape')->download("{$nom}.pdf");
    }

    public function ficheD(Request $request): Response
    {
        $this->authorize('presence.reports');
        $data = $request->validate([
            'structure_id' => ['nullable', 'exists:structures,id'],
            'trimestre'    => ['required', 'integer', 'between:1,4'],
            'annee'        => ['required', 'integer', 'between:2000,2100'],
            'format'       => ['nullable', 'in:pdf,xlsx'],
        ]);

        $structureId = ! empty($data['structure_id']) ? (int) $data['structure_id'] : null;
        $fiche = $this->service->ficheD($structureId, (int) $data['trimestre'], (int) $data['annee']);
        $nom = "fiche_D_{$data['annee']}_T{$data['trimestre']}";

        if (($data['format'] ?? 'pdf') === 'xlsx') {
            return Excel::download(new FicheExcelExport('Fiche D', $this->entetesRecap(), $this->lignesRecap($fiche['lignes'])), "{$nom}.xlsx");
        }

        return Pdf::loadView('fiches.pdf.fiche-d', $fiche)->setPaper('a4', 'landscape')->download("{$nom}.pdf");
    }

    /** Entêtes Excel des récapitulatifs (fiches C et D). */
    private function entetesRecap(): array
    {
        return ['N°', 'Nom et Prénoms', 'Matricule', 'Emploi', 'Fonction', 'Structure', 'Absence (Heure)', 'Absence (Jour)', 'Mesures prises', 'Référence pièces justificatives'];
    }

    /** Lignes Excel des récapitulatifs (fiches C et D). */
    private function lignesRecap(array $lignes): array
    {
        return array_map(fn ($l) => [
            $l['n'], $l['nom'], $l['matricule'], $l['emploi'], $l['fonction'], $l['structure'],
            $l['total_heures'], $l['total_jours'], $l['mesures'], $l['references'],
        ], $lignes);
    }
}
