<?php

namespace App\Http\Controllers;

use App\Exports\AgentsExport;
use App\Imports\AgentsImport;
use App\Services\AgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgentImportExportController extends Controller
{
    public function __construct(private AgentService $service) {}

    public function form(): View
    {
        $this->authorize('agents.import');
        return view('agents.import');
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
        if (! empty($import->erreurs)) {
            return redirect()->route('agents.index')
                ->with('success', $message)
                ->with('error', implode(' ', array_slice($import->erreurs, 0, 10)));
        }

        return redirect()->route('agents.index')->with('success', $message);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('agents.export');

        $filtres = $request->only(['q', 'region', 'statut_dossier']);
        $nom = 'agents_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AgentsExport($filtres), $nom);
    }
}
