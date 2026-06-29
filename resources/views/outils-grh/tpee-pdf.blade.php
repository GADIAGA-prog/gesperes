<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 16px 20px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { text-align: center; font-size: 13px; margin: 6px 0 2px; }
        .sous-titre { text-align: center; font-size: 9px; color: #555; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; }
        th { background: #1d4e89; color: #fff; font-size: 8px; text-align: center; }
        td.lib { text-align: left; }
        td.num, th.num { text-align: right; }
        tfoot td { background: #f3f5f8; font-weight: bold; }
        .pos { color: #b91c1c; } .neg { color: #b45309; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete', ['avecDrh' => true])

    <h1>TABLEAU PRÉVISIONNEL DES EFFECTIFS ET DES EMPLOIS (TPEE)</h1>
    <div class="sous-titre">
        {{ $structure?->libelle ?? 'Niveau national' }} —
        projection {{ $tableau['annees'][0] }}–{{ end($tableau['annees']) }} — édité le {{ now()->translatedFormat('d F Y') }}
    </div>

    @php $annees = $tableau['annees']; @endphp
    <table>
        <thead>
            <tr>
                <th rowspan="2">Emploi</th>
                <th rowspan="2" class="num">Effectif<br>{{ now()->year }}</th>
                @foreach ($annees as $an)<th colspan="5">{{ $an }}</th>@endforeach
            </tr>
            <tr>
                @foreach ($annees as $an)
                    <th class="num">Départs</th><th class="num">Entrées</th><th class="num">Prév.</th><th class="num">Cible</th><th class="num">Écart</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tableau['lignes'] as $ligne)
                <tr>
                    <td class="lib">{{ $ligne['emploi']->libelle }}</td>
                    <td class="num">{{ $ligne['effectif'] }}</td>
                    @foreach ($annees as $an)
                        @php $c = $ligne['annees'][$an]; @endphp
                        <td class="num">{{ $c['dep'] ?: '' }}</td>
                        <td class="num">{{ $c['ent'] ?: '' }}</td>
                        <td class="num">{{ $c['fin'] }}</td>
                        <td class="num">{{ $c['cible'] ?? '' }}</td>
                        <td class="num {{ is_null($c['ecart']) ? '' : ($c['ecart'] > 0 ? 'pos' : ($c['ecart'] < 0 ? 'neg' : '')) }}">{{ is_null($c['ecart']) ? '' : $c['ecart'] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ 2 + count($annees) * 5 }}" style="text-align:center;color:#777;">Aucun emploi à projeter.</td></tr>
            @endforelse
        </tbody>
        @if (! empty($tableau['lignes']))
            <tfoot>
                <tr>
                    <td class="lib">Total</td>
                    <td class="num">{{ $tableau['total_effectif'] }}</td>
                    @foreach ($annees as $an)
                        @php $t = $tableau['totaux'][$an]; @endphp
                        <td class="num">{{ $t['dep'] }}</td>
                        <td class="num">{{ $t['ent'] }}</td>
                        <td class="num">{{ $t['fin'] }}</td>
                        <td class="num">{{ $t['cible'] ?: '' }}</td>
                        <td class="num">{{ $t['ecart'] ?: '' }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
