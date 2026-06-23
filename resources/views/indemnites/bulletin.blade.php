<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { text-align: center; font-size: 14px; margin: 10px 0 2px; }
        .sous-titre { text-align: center; font-size: 10px; color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .id td { border: none; padding: 2px 4px; }
        .id .label { color: #555; width: 18%; }
        .lignes th, .lignes td { border: 1px solid #ccc; padding: 5px 8px; }
        .lignes th { background: #1d4e89; color: #fff; font-size: 10px; text-align: left; }
        .montant { text-align: right; }
        .sect { background: #f3f5f8; font-weight: bold; }
        .brut td { background: #1d4e89; color: #fff; font-weight: bold; font-size: 12px; }
        .pied { margin-top: 18px; font-size: 9px; color: #777; text-align: center; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete', ['avecDrh' => true])

    <h1>BULLETIN DE RÉMUNÉRATION</h1>
    <div class="sous-titre">Estimation indicative — {{ now()->translatedFormat('F Y') }}</div>

    <table class="id" style="margin-bottom:10px;">
        <tr>
            <td class="label">Agent</td><td><strong>{{ $agent->nom_complet }}</strong> ({{ $agent->matricule }}{{ $agent->cle }})</td>
            <td class="label">Emploi</td><td>{{ $agent->emploi?->libelle ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Catégorie</td><td>{{ $agent->categorie?->code ?? '—' }} / {{ $agent->echelle?->libelle ?? '—' }} / {{ $agent->echelon?->libelle ?? '—' }}</td>
            <td class="label">Indice</td><td>{{ $agent->indice?->valeur ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Structure</td><td>{{ $agent->structure?->libelle ?? '—' }}</td>
            <td class="label">Localité / Zone</td><td>{{ $agent->localite?->libelle ?? '—' }} / {{ $agent->localite?->zone?->libelle ?? '—' }}</td>
        </tr>
    </table>

    <table class="lignes">
        <thead>
            <tr><th>Élément de rémunération</th><th class="montant" style="width:30%;">Montant (FCFA)</th></tr>
        </thead>
        <tbody>
            <tr class="sect"><td>Traitement indiciaire</td><td class="montant">{{ number_format($salaire, 0, ',', ' ') }}</td></tr>
            <tr class="sect"><td colspan="2">Indemnités (décret 2014-427)</td></tr>
            @forelse ($indemnites as $i)
                <tr>
                    <td>&nbsp;&nbsp;{{ $i['indemnite']->libelle }}</td>
                    <td class="montant">{{ number_format($i['montant'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td>&nbsp;&nbsp;Aucune indemnité applicable</td><td class="montant">0</td></tr>
            @endforelse
            <tr><td>Total indemnités</td><td class="montant">{{ number_format($totalIndem, 0, ',', ' ') }}</td></tr>
            <tr class="brut"><td>RÉMUNÉRATION BRUTE ESTIMÉE</td><td class="montant">{{ number_format($brut, 0, ',', ' ') }}</td></tr>
        </tbody>
    </table>

    <div class="pied">
        Document indicatif généré par GesPerES / MESFPTT le {{ now()->translatedFormat('d F Y à H:i') }}.
        Les montants sont issus de la grille indiciaire et des barèmes du décret 2014-427 ; ils ne constituent pas un bulletin de paie officiel.
    </div>
</body>
</html>
