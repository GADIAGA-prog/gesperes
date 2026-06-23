<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { text-align: center; font-size: 13px; margin: 8px 0 4px; }
        .meta { font-size: 10px; margin: 6px 0; }
        table.grille { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.grille th, table.grille td { border: 1px solid #444; padding: 3px 4px; }
        table.grille th { background: #eee; font-size: 9px; text-align: center; }
        .c { text-align: center; }
        .sign { margin-top: 24px; font-size: 10px; text-align: right; padding-right: 30px; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete')

    <h1>FICHE A : SITUATION JOURNALIÈRE DE PRÉSENCE DES AGENTS</h1>

    <div class="meta">
        <strong>STRUCTURE :</strong> {{ $structure?->libelle ?? '—' }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date :</strong> {{ $date->translatedFormat('l d F Y') }}
    </div>

    <table class="grille">
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom et Prénom(s)</th>
                <th>Matricule</th>
                <th>Emploi</th>
                <th>Fonction</th>
                <th>Présent</th>
                <th>Absent</th>
                <th>Durée absence<br>(Heure)</th>
                <th>Durée absence<br>(Jour)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lignes as $l)
                <tr>
                    <td class="c">{{ $l['n'] }}</td>
                    <td>{{ $l['nom'] }}</td>
                    <td>{{ $l['matricule'] }}</td>
                    <td>{{ $l['emploi'] ?: '' }}</td>
                    <td>{{ $l['fonction'] ?: '' }}</td>
                    <td class="c">{{ $l['present'] === true ? 'X' : '' }}</td>
                    <td class="c">{{ $l['absent'] ? 'X' : '' }}</td>
                    <td class="c">{{ $l['duree_heures'] }}</td>
                    <td class="c">{{ $l['duree_jours'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="c">Aucun agent.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sign">
        Fait à ……………………… , le ……………………<br><br>
        Signature du supérieur hiérarchique immédiat
    </div>
</body>
</html>
