<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111; }
        h1 { text-align: center; font-size: 13px; margin: 8px 0; text-transform: uppercase; }
        .section-titre { background: #1d4e89; color: #fff; font-size: 10px; font-weight: bold;
                         padding: 4px 6px; text-transform: uppercase; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        td, th { border: 1px solid #bbb; padding: 3px 6px; vertical-align: top; }
        td.label { background: #f3f5f8; color: #555; width: 26%; font-weight: bold; }
        .center { text-align: center; }
        .pied { margin-top: 16px; font-size: 8.5px; color: #777; text-align: center; }
        ul { margin: 2px 0; padding-left: 16px; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete', ['avecDrh' => false])

    <h1>Fiche de poste de travail</h1>

    <div class="section-titre">Descriptif du poste — Identification</div>
    <table>
        <tr><td class="label">Intitulé du poste</td><td colspan="3">{{ $fiche->intitule }}</td></tr>
        <tr><td class="label">Code du poste</td><td>{{ $fiche->code ?: '—' }}</td>
            <td class="label">Catégorie</td><td>{{ $fiche->categorie?->code ?? '—' }}</td></tr>
        <tr><td class="label">Unité administrative</td><td colspan="3">{{ $fiche->structure?->cheminComplet() ?? '—' }}</td></tr>
        <tr><td class="label">Famille professionnelle</td><td>{{ $fiche->familleProfessionnelle?->libelle ?? '—' }}</td>
            <td class="label">Emploi-type</td><td>{{ $fiche->emploiType?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">Famille d'emplois</td><td>{{ $fiche->famille_emplois ?? '—' }}</td>
            <td class="label">Emploi</td><td>{{ $fiche->emploi?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">Type de poste</td><td>{{ $fiche->type_poste?->label() ?? '—' }}</td>
            <td class="label">Position / mission</td><td>{{ $fiche->position_mission?->label() ?? '—' }}</td></tr>
    </table>

    <div class="section-titre">Mission du poste</div>
    <table><tr><td>{{ $fiche->mission ?: '—' }}</td></tr></table>

    <div class="section-titre">Activités permanentes</div>
    <table>
        @forelse ($fiche->activites as $a)
            <tr><td>{{ $a->libelle }}</td><td style="width:18%">{{ $a->taux_contribution ?: '' }}</td></tr>
        @empty
            <tr><td>—</td></tr>
        @endforelse
    </table>

    <div class="section-titre">Relations du poste</div>
    <table>
        <tr><td class="label">Niveau hiérarchique supérieur</td><td>{{ $fiche->niveau_hierarchique_superieur ?: '—' }}</td></tr>
        <tr><td class="label">Niveau hiérarchique inférieur</td><td>{{ $fiche->niveau_hierarchique_inferieur ?: '—' }}</td></tr>
        <tr><td class="label">Relations fonctionnelles internes</td><td>{{ $fiche->relations_internes ?: '—' }}</td></tr>
        <tr><td class="label">Relations fonctionnelles externes</td><td>{{ $fiche->relations_externes ?: '—' }}</td></tr>
    </table>

    <div class="section-titre">Profil du poste — Dimension</div>
    <table>
        <tr><td class="label">Moyens généraux</td><td>{{ $fiche->moyens_generaux ?: '—' }}</td></tr>
        <tr><td class="label">Moyens spécifiques</td><td>{{ $fiche->moyens_specifiques ?: '—' }}</td></tr>
    </table>

    <div class="section-titre">Compétences requises</div>
    <table>
        <tr><th style="text-align:left">Compétence</th><th class="center" style="width:16%">Application</th>
            <th class="center" style="width:16%">Analyse</th><th class="center" style="width:16%">Expertise</th></tr>
        @forelse ($fiche->competences as $c)
            @php $n = $c->pivot->niveau; @endphp
            <tr>
                <td>{{ $c->libelle }}</td>
                <td class="center">{{ $n === 'application' ? 'X' : '' }}</td>
                <td class="center">{{ $n === 'analyse' ? 'X' : '' }}</td>
                <td class="center">{{ $n === 'expertise' ? 'X' : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">—</td></tr>
        @endforelse
    </table>

    <div class="section-titre">Conditions d'accès au poste</div>
    <table>
        <tr><td class="label">Niveau d'études</td><td>{{ $fiche->niveau_etudes ?: '—' }}</td>
            <td class="label">Domaine</td><td>{{ $fiche->domaine ?: '—' }}</td></tr>
        <tr><td class="label">Spécialité</td><td>{{ $fiche->specialite ?: '—' }}</td>
            <td class="label">Expérience professionnelle</td><td>{{ $fiche->experience_pro ?: '—' }}</td></tr>
    </table>

    <div class="section-titre">Indicateurs de performance du poste</div>
    <table>
        @forelse ($fiche->indicateurs as $i)
            <tr><td>{{ $i->libelle }}</td><td style="width:22%">{{ $i->nature ? ucfirst($i->nature) : '' }}</td></tr>
        @empty
            <tr><td>—</td></tr>
        @endforelse
    </table>

    <div class="section-titre">Validation</div>
    <table>
        <tr><td class="label">Supérieur hiérarchique immédiat</td><td>Nom et prénom : ………………………… Signature :</td></tr>
        <tr><td class="label">Direction des Ressources Humaines</td><td>Version : {{ $fiche->version ?: '—' }} — Nom et prénom : ………………… Signature :</td></tr>
    </table>

    <div class="pied">
        Document généré le {{ now()->translatedFormat('d F Y à H:i') }} — GesPerES / MESFPTT
    </div>
</body>
</html>
