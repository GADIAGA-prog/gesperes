<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { text-align: center; font-size: 12px; margin: 8px 0 2px; }
        .meta { font-size: 9px; margin: 4px 0 8px; }
        table.g { width: 100%; border-collapse: collapse; }
        table.g th, table.g td { border: 1px solid #555; padding: 2px 3px; vertical-align: top; }
        table.g th { background: #eee; font-size: 8px; text-align: center; }
        .c { text-align: center; } .r { text-align: right; }
        .sign { margin-top: 20px; font-size: 9px; text-align: right; padding-right: 30px; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete', ['avecDrh' => true])

    <h1>PROGRAMME D'ACTIVITÉ ET BUDGET — {{ $exercice }}</h1>
    <div class="meta">
        <strong>Structure :</strong> {{ $structure?->libelle ?? 'Toutes les structures' }}
    </div>

    <table class="g">
        <thead>
            <tr>
                <th>Code</th>
                <th>Activité</th>
                <th>Programme / Action</th>
                <th>Indicateur</th>
                <th>Cible</th>
                <th>Localité</th>
                <th>Montant</th>
                <th>AE</th>
                <th>CP</th>
                <th>T1</th><th>T2</th><th>T3</th><th>T4</th>
            </tr>
        </thead>
        <tbody>
            @php $tMontant = 0; $tAe = 0; $tCp = 0; @endphp
            @forelse ($activites as $a)
                @php $tMontant += $a->montant; $tAe += $a->total_ae; $tCp += $a->total_cp; @endphp
                <tr>
                    <td class="c">{{ $a->code }}</td>
                    <td>{{ $a->libelle }}</td>
                    <td>{{ $a->action?->programme?->code }} / {{ $a->action?->code }}</td>
                    <td>{{ $a->indicateur }}</td>
                    <td class="c">{{ $a->cible }}</td>
                    <td>{{ $a->localite }}</td>
                    <td class="r">{{ number_format($a->montant, 0, ',', ' ') }}</td>
                    <td class="r">{{ number_format($a->total_ae, 0, ',', ' ') }}</td>
                    <td class="r">{{ number_format($a->total_cp, 0, ',', ' ') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($a->trimestre_1, 2, '.', ''), '0'), '.') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($a->trimestre_2, 2, '.', ''), '0'), '.') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($a->trimestre_3, 2, '.', ''), '0'), '.') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($a->trimestre_4, 2, '.', ''), '0'), '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="13" class="c">Aucune activité.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;">
                <td colspan="6" class="r">TOTAUX</td>
                <td class="r">{{ number_format($tMontant, 0, ',', ' ') }}</td>
                <td class="r">{{ number_format($tAe, 0, ',', ' ') }}</td>
                <td class="r">{{ number_format($tCp, 0, ',', ' ') }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <div class="sign">
        Fait à ……………………… , le ……………………<br><br>
        Le Responsable de la structure
    </div>
</body>
</html>
