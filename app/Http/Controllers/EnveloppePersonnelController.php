<?php

namespace App\Http\Controllers;

use App\Models\EnveloppePersonnel;
use App\Services\VentilationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sous-module Budget : enveloppe de référence (DPBEP) des dépenses de personnel
 * sur 3 exercices (n+1 à n+3), fournie par l'utilisateur, présentée à la manière
 * du fichier « Répartition Dépenses du personnel ».
 */
class EnveloppePersonnelController extends Controller
{
    public function index(): View
    {
        $this->authorize('budget.view');

        return view('budget.enveloppe.index', [
            'enveloppes' => EnveloppePersonnel::withCount('lignes')->orderByDesc('annee_debut')->get(),
            'anneeDefaut' => (int) now()->year + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('budget.manage');

        $data = $request->validate([
            'annee_debut' => ['required', 'integer', 'between:2000,2100'],
            'intitule'    => ['nullable', 'string', 'max:255'],
        ]);

        $enveloppe = EnveloppePersonnel::create([
            'annee_debut' => $data['annee_debut'],
            'intitule'    => $data['intitule'] ?: "Enveloppe de référence du DPBEP des dépenses de personnel",
            'actif'       => true,
        ]);

        // Lignes par défaut (modèle du fichier de référence).
        foreach (['IDR', 'Régularisation de situations salaires des agents en cessation de paiement', 'Salaire du personnel en activité'] as $i => $libelle) {
            $enveloppe->lignes()->create(['libelle' => $libelle, 'ordre' => $i]);
        }

        return redirect()->route('budget.enveloppe.show', $enveloppe)
            ->with('success', 'Enveloppe créée. Renseignez les montants.');
    }

    public function show(EnveloppePersonnel $enveloppe): View
    {
        $this->authorize('budget.view');
        $enveloppe->load('lignes');

        return view('budget.enveloppe.show', ['enveloppe' => $enveloppe]);
    }

    /** Ventilation détaillée de l'enveloppe par action et paragraphe de dépense. */
    public function ventilation(EnveloppePersonnel $enveloppe, VentilationService $ventilation): View
    {
        $this->authorize('budget.view');
        $enveloppe->load('lignes');

        return view('budget.enveloppe.ventilation', [
            'enveloppe'   => $enveloppe,
            'ventilation' => $ventilation->ventiler($enveloppe),
        ]);
    }

    public function update(Request $request, EnveloppePersonnel $enveloppe): RedirectResponse
    {
        $this->authorize('budget.manage');

        $data = $request->validate([
            'intitule'        => ['nullable', 'string', 'max:255'],
            'lignes'          => ['array'],
            'lignes.*.libelle'    => ['nullable', 'string', 'max:255'],
            'lignes.*.montant_n1' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_n2' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_n3' => ['nullable', 'numeric', 'min:0'],
        ]);

        $enveloppe->update(['intitule' => $data['intitule'] ?: $enveloppe->intitule]);

        // Remplace l'ensemble des lignes (celles dont le libellé est renseigné).
        $enveloppe->lignes()->delete();
        foreach ($data['lignes'] ?? [] as $i => $ligne) {
            if (trim((string) ($ligne['libelle'] ?? '')) === '') {
                continue;
            }
            $enveloppe->lignes()->create([
                'libelle'    => $ligne['libelle'],
                'montant_n1' => $ligne['montant_n1'] ?? 0,
                'montant_n2' => $ligne['montant_n2'] ?? 0,
                'montant_n3' => $ligne['montant_n3'] ?? 0,
                'ordre'      => $i,
            ]);
        }

        return redirect()->route('budget.enveloppe.show', $enveloppe)
            ->with('success', 'Enveloppe mise à jour.');
    }

    public function destroy(EnveloppePersonnel $enveloppe): RedirectResponse
    {
        $this->authorize('budget.manage');
        $enveloppe->delete();

        return redirect()->route('budget.enveloppe.index')->with('success', 'Enveloppe supprimée.');
    }
}
