<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 16px 20px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { text-align: center; font-size: 13px; margin: 8px 0 2px; }
        .meta { font-size: 9px; color: #555; margin: 4px 0 8px; }
        .meta strong { color: #111; }
        table.grille { width: 100%; border-collapse: collapse; }
        table.grille th, table.grille td { border: 1px solid #999; padding: 3px 4px; }
        table.grille th { background: #1d4e89; color: #fff; font-size: 8px; text-align: left; }
        table.grille tr:nth-child(even) td { background: #f5f7fa; }
        .c { text-align: center; }
        .mono { font-family: monospace; }
        .pied { margin-top: 10px; font-size: 8px; color: #777; text-align: center; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete')

    <h1>LISTE DES AGENTS</h1>

    <div class="meta">
        <strong>{{ $agents->count() }}</strong> agent(s)
        @if (! empty($filtres['q'])) &nbsp;|&nbsp; Recherche : <strong>{{ $filtres['q'] }}</strong> @endif
        @if (! empty($filtres['region'])) &nbsp;|&nbsp; Région : <strong>{{ $filtres['region'] }}</strong> @endif
        @if (! empty($filtres['statut_dossier'])) &nbsp;|&nbsp; Statut : <strong>{{ \App\Enums\StatutDossier::tryFrom($filtres['statut_dossier'])?->label() }}</strong> @endif
        &nbsp;|&nbsp; Édité le {{ now()->format('d/m/Y H:i') }}
    </div>

    <table class="grille">
        <thead>
            <tr>
                <th class="c">N°</th>
                <th>Matricule</th>
                <th>Nom et Prénoms</th>
                <th class="c">Sexe</th>
                <th>Emploi</th>
                <th class="c">Cat.</th>
                <th>Structure</th>
                <th>Région</th>
                <th>Établissement</th>
                <th>Dossier</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agents as $i => $agent)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="mono">{{ $agent->matricule }}{{ $agent->cle }}</td>
                    <td>{{ $agent->nom_complet }}</td>
                    <td class="c">{{ $agent->sexe?->value }}</td>
                    <td>{{ $agent->emploi?->libelle }}</td>
                    <td class="c">{{ $agent->categorie?->code }}</td>
                    <td>{{ $agent->structure?->libelle }}</td>
                    <td>{{ $agent->region }}</td>
                    <td>{{ $agent->etablissement }}</td>
                    <td>{{ $agent->statut_dossier?->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="c">Aucun agent.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pied">
        Document généré par GesPerES / MESFPTT — {{ now()->translatedFormat('d F Y à H:i') }}
    </div>
</body>
</html>
