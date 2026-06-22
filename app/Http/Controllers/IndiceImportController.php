<?php

namespace App\Http\Controllers;

use App\Exports\IndicesTemplateExport;
use App\Imports\IndicesImport;
use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import des indices (catégorie × classe × échelon) depuis Excel/CSV.
 */
class IndiceImportController extends Controller
{
    public function form(): View
    {
        $this->authorize('settings.manage');

        return view('referentiels.indices-import', [
            'categories' => Categorie::orderBy('code')->get(['code', 'libelle']),
            'classes'    => Classe::orderBy('code')->get(['code', 'libelle']),
            'echelons'   => Echelon::orderBy('rang')->get(['code', 'libelle']),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('settings.manage');

        $request->validate([
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [], ['fichier' => 'fichier']);

        $import = new IndicesImport();
        Excel::import($import, $request->file('fichier'));

        $message = "{$import->importes} indice(s) créé(s), {$import->maj} mis à jour.";
        $redirect = redirect()->route('referentiels.show', 'indices')->with('success', $message);

        if (! empty($import->erreurs)) {
            $redirect->with('error', implode(' ', array_slice($import->erreurs, 0, 10)));
        }

        return $redirect;
    }

    public function template(): BinaryFileResponse
    {
        $this->authorize('settings.manage');

        return Excel::download(new IndicesTemplateExport(), 'modele_import_indices.xlsx');
    }
}
