<?php

namespace App\Http\Controllers;

use App\Exports\AgentsExport;
use App\Exports\ModeleImportAgents;
use App\Imports\AgentsImport;
use App\Models\Agent;
use App\Services\AgentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentImportExportController extends Controller
{
    public function __construct(private AgentService $service) {}

    public function form(): View
    {
        $this->authorize('agents.import');
        return view('agents.import', ['colonnesModele' => ModeleImportAgents::COLONNES]);
    }

    /** Modèle Excel vierge à respecter pour l'import. */
    public function modele(): BinaryFileResponse
    {
        $this->authorize('agents.import');

        return Excel::download(new ModeleImportAgents(), 'modele_import_agents.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('agents.import');

        $request->validate([
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [], ['fichier' => 'fichier']);

        $import = new AgentsImport($this->service, $request->user()->id);
        Excel::import($import, $request->file('fichier'));

        $message = "{$import->importes} agent(s) importé(s).";
        if ($import->ignores > 0) {
            $message .= " {$import->ignores} doublon(s) ignoré(s).";
        }
        if (! empty($import->erreurs)) {
            return redirect()->route('agents.index')
                ->with('success', $message)
                ->with('error', implode(' ', array_slice($import->erreurs, 0, 10)));
        }

        return redirect()->route('agents.index')->with('success', $message);
    }

    /**
     * Export CSV streamé des agents (filtres + colonnes choisies de l'index).
     * Streamé ligne par ligne via un curseur paresseux : mémoire bornée et pas
     * de délai d'exécution, indispensable pour exporter toute la base (~43 000
     * agents) — un .xlsx en mémoire saturait la RAM (HTTP 500).
     * Le fichier .csv (BOM UTF-8 + séparateur « ; ») s'ouvre directement dans Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('agents.export');

        $filtres = $request->only(['q', 'region', 'statut_dossier']);
        $colonnes = (array) $request->input('colonnes', []);

        $export = new AgentsExport($filtres, $colonnes);
        $cols = $export->colonnesResolues();
        $requete = $export->query();

        $nom = 'agents_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($cols, $requete) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF"); // BOM UTF-8 (accents corrects dans Excel)
            fputcsv($sortie, array_map(fn ($c) => $c['label'], $cols), ';', '"', '');

            foreach ($requete->lazy(1000) as $agent) {
                fputcsv($sortie, array_map(fn ($c) => (string) ($c['valeur']($agent) ?? ''), $cols), ';', '"', '');
            }

            fclose($sortie);
        }, $nom, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nom . '"',
        ]);
    }

    /** Fiche individuelle d'un agent au format PDF. */
    public function exportPdfFiche(Agent $agent): Response
    {
        $this->authorize('agents.view');

        $agent->load([
            'emploi', 'fonction', 'poste', 'categorie', 'echelle', 'classe', 'echelon',
            'indice', 'positionAdministrative', 'structure', 'localite', 'typeEnseignement',
            'specialite',
        ]);

        $nom = 'fiche_' . $agent->matricule . '_' . now()->format('Ymd') . '.pdf';

        return Pdf::loadView('agents.pdf.fiche', ['agent' => $agent])
            ->setPaper('a4', 'portrait')
            ->download($nom);
    }

    /** Liste filtrée des agents au format PDF (mêmes filtres que l'index). */
    public function exportPdfListe(Request $request): Response
    {
        $this->authorize('agents.export');

        $filtres = $request->only(['q', 'region', 'statut_dossier']);

        $agents = Agent::query()
            ->with(['emploi', 'structure', 'categorie'])
            ->recherche($filtres['q'] ?? null)
            ->region($filtres['region'] ?? null)
            ->when(! empty($filtres['statut_dossier']), fn ($query) =>
                $query->where('statut_dossier', $filtres['statut_dossier']))
            ->orderBy('nom')
            ->get();

        $nom = 'agents_' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('agents.pdf.liste', [
            'agents'  => $agents,
            'filtres' => $filtres,
        ])->setPaper('a4', 'landscape')->download($nom);
    }
}
