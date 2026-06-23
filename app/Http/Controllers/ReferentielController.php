<?php

namespace App\Http\Controllers;

use App\Exports\FicheExcelExport;
use App\Exports\ReferentielExport;
use App\Imports\ReferentielImport;
use App\Support\ReferentielRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CRUD générique des référentiels, piloté par App\Support\ReferentielRegistry.
 * Un seul contrôleur gère toutes les nomenclatures (catégories, échelles, emplois, etc.).
 */
class ReferentielController extends Controller
{
    public function index(): View
    {
        $this->authorize('settings.view');

        $groupes = ReferentielRegistry::groupes();
        $actif = request()->query('groupe');
        $afficher = ($actif && isset($groupes[$actif])) ? [$actif => $groupes[$actif]] : $groupes;

        return view('referentiels.index', ['groupes' => $afficher]);
    }

    public function show(string $type, Request $request): View
    {
        $this->authorize('settings.view');
        $config = $this->config($type);

        $model = $config['model'];
        $items = $model::orderBy('libelle')->paginate(25);

        return view('referentiels.show', [
            'type'    => $type,
            'config'  => $config,
            'items'   => $items,
            'sources' => $this->sources($config),
            'edition' => $request->filled('edit') ? $model::find($request->input('edit')) : null,
        ]);
    }

    public function store(string $type, Request $request): RedirectResponse
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        $data = $this->valider($type, $config, $request);
        $config['model']::create($data);

        return redirect()->route('referentiels.show', $type)
            ->with('success', "{$config['singulier']} créé(e).");
    }

    public function update(string $type, int $id, Request $request): RedirectResponse
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        $item = $config['model']::findOrFail($id);
        $data = $this->valider($type, $config, $request, $id);
        $item->update($data);

        return redirect()->route('referentiels.show', $type)
            ->with('success', "{$config['singulier']} mis(e) à jour.");
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        $config['model']::findOrFail($id)->delete();

        return redirect()->route('referentiels.show', $type)
            ->with('success', "{$config['singulier']} supprimé(e).");
    }

    public function export(string $type): BinaryFileResponse
    {
        $this->authorize('settings.view');
        $config = $this->config($type);

        $nom = $type . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ReferentielExport($type, $config), $nom);
    }

    public function importForm(string $type): View
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        return view('referentiels.import', [
            'type'    => $type,
            'config'  => $config,
            'entetes' => ReferentielExport::entetes($config),
            'sources' => $this->sources($config),
        ]);
    }

    public function import(string $type, Request $request): RedirectResponse
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        $request->validate([
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [], ['fichier' => 'fichier']);

        $import = new ReferentielImport($type, $config);
        Excel::import($import, $request->file('fichier'));

        $redirect = redirect()->route('referentiels.show', $type)
            ->with('success', "{$import->importes} créé(s), {$import->maj} mis à jour.");

        if (! empty($import->erreurs)) {
            $redirect->with('error', implode(' ', array_slice($import->erreurs, 0, 10)));
        }

        return $redirect;
    }

    public function modele(string $type): BinaryFileResponse
    {
        $this->authorize('settings.manage');
        $config = $this->config($type);

        // Modèle vierge : uniquement la ligne d'en-têtes attendue.
        $export = new FicheExcelExport($config['titre'], ReferentielExport::entetes($config), []);

        return Excel::download($export, "modele_{$type}.xlsx");
    }

    private function config(string $type): array
    {
        $config = ReferentielRegistry::get($type);
        if (! $config) {
            throw new NotFoundHttpException("Référentiel « {$type} » inconnu.");
        }
        return $config;
    }

    private function valider(string $type, array $config, Request $request, ?int $id = null): array
    {
        $table = (new $config['model'])->getTable();
        $uniqueCode = 'unique:' . $table . ',code' . ($id ? ',' . $id : '');

        $rules = [
            'code'    => ['required', 'string', 'max:60', $uniqueCode],
            'libelle' => ['required', 'string', 'max:255'],
            'actif'   => ['nullable', 'boolean'],
        ];

        foreach ($config['champs'] as $nom => $def) {
            $rules[$nom] = match ($def['type']) {
                'number'  => ['nullable', 'integer'],
                'boolean' => ['nullable', 'boolean'],
                'select'  => ['nullable', 'integer'],
                'enum'    => ['nullable', \Illuminate\Validation\Rule::in(array_map(fn ($c) => $c->value, $def['enum']::cases()))],
                default   => ['nullable', 'string', 'max:255'],
            };
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        // normalisation des cases à cocher
        $validated['actif'] = $request->boolean('actif');
        foreach ($config['champs'] as $nom => $def) {
            if ($def['type'] === 'boolean') {
                $validated[$nom] = $request->boolean($nom);
            }
        }

        return $validated;
    }

    private function sources(array $config): array
    {
        $sources = [];
        foreach ($config['champs'] as $nom => $def) {
            if (($def['type'] ?? null) === 'select' && isset($def['source'])) {
                $sources[$nom] = $def['source']::orderBy('libelle')->pluck('libelle', 'id');
            }
            if (($def['type'] ?? null) === 'enum' && isset($def['enum'])) {
                $sources[$nom] = collect($def['enum']::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]);
            }
        }
        return $sources;
    }
}
