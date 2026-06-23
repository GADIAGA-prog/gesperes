<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { text-align: center; font-size: 14px; margin: 10px 0 2px; }
        .sous-titre { text-align: center; font-size: 10px; color: #555; margin-bottom: 10px; }
        .identite { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .identite td { border: none; vertical-align: middle; }
        .avatar { width: 46px; height: 46px; border-radius: 50%; background: #e8eef6;
                  color: #1d4e89; text-align: center; font-size: 22px; font-weight: bold; }
        .nom { font-size: 13px; font-weight: bold; }
        .matricule { font-family: monospace; color: #555; }
        .bloc { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .bloc-titre { background: #1d4e89; color: #fff; font-size: 10px; font-weight: bold;
                      padding: 4px 6px; text-transform: uppercase; }
        .bloc td { border: 1px solid #ccc; padding: 3px 6px; }
        .bloc td.label { background: #f3f5f8; color: #555; width: 22%; }
        .bloc td.valeur { width: 28%; }
        .observations { border: 1px solid #ccc; padding: 6px; font-size: 10px; white-space: pre-line; }
        .pied { margin-top: 18px; font-size: 9px; color: #777; text-align: center; }
    </style>
</head>
<body>
    @include('fiches.pdf._entete', ['avecDrh' => true])

    <h1>FICHE INDIVIDUELLE DE L'AGENT</h1>
    <div class="sous-titre">Gestion du Personnel Enseignant du Secondaire — GesPerES</div>

    <table class="identite">
        <tr>
            <td style="width:54px;"><div class="avatar">{{ strtoupper(substr($agent->nom, 0, 1)) }}</div></td>
            <td>
                <span class="nom">{{ $agent->nom_complet }}</span><br>
                <span class="matricule">{{ $agent->matricule }}{{ $agent->cle }}</span>
                &nbsp;—&nbsp; {{ $agent->statut_dossier?->label() ?? '—' }}
            </td>
        </tr>
    </table>

    @php
        $blocs = [
            'État civil' => [
                'Sexe' => $agent->sexe?->label(),
                'Date de naissance' => $agent->date_naissance?->format('d/m/Y'),
                'Âge' => $agent->age ? $agent->age . ' ans' : null,
                'Nationalité' => $agent->nationalite,
                'Téléphone' => $agent->telephone,
                'E-mail' => $agent->email,
                'Adresse' => $agent->adresse,
            ],
            'Carrière' => [
                'Emploi' => $agent->emploi?->libelle,
                'Fonction' => $agent->fonction?->libelle,
                'Poste' => $agent->poste?->libelle,
                'Catégorie' => $agent->categorie?->code,
                'Échelle' => $agent->echelle?->libelle,
                'Classe' => $agent->classe?->libelle,
                'Échelon' => $agent->echelon?->libelle,
                'Indice' => $agent->indice?->valeur,
                'Position' => $agent->positionAdministrative?->libelle,
                'Date intégration' => $agent->date_integration?->format('d/m/Y'),
                'Date retraite' => $agent->date_retraite?->format('d/m/Y'),
            ],
            'Affectation' => [
                'Structure' => $agent->structure?->libelle,
                'Région' => $agent->region,
                'Province' => $agent->province,
                'Commune' => $agent->commune,
                'Établissement' => $agent->etablissement,
                'Localité' => $agent->localite?->libelle,
                'Date affectation' => $agent->date_affectation?->format('d/m/Y'),
            ],
            'Enseignement' => [
                'Type' => $agent->typeEnseignement?->libelle,
                'Spécialité' => $agent->specialite?->libelle,
                'Lieu d\'exercice' => $agent->lieu_exercice?->label(),
                'Volume horaire dû' => $agent->volume_horaire_du,
                'Volume horaire assuré' => $agent->volume_horaire_assure,
            ],
            'Famille' => [
                'Situation' => $agent->situation_matrimoniale?->label(),
                'Nombre d\'enfants' => $agent->nombre_enfants,
                'Personnes à charge' => $agent->personnes_a_charge,
                'Allocation familiale' => $agent->allocation_familiale
                    ? number_format($agent->allocation_familiale, 0, ',', ' ') . ' FCFA' : null,
            ],
        ];
    @endphp

    @foreach ($blocs as $titre => $champs)
        <table class="bloc">
            <tr><td class="bloc-titre" colspan="4">{{ $titre }}</td></tr>
            @foreach (collect($champs)->chunk(2) as $paire)
                <tr>
                    @foreach ($paire as $label => $valeur)
                        <td class="label">{{ $label }}</td>
                        <td class="valeur">{{ $valeur !== null && $valeur !== '' ? $valeur : '—' }}</td>
                    @endforeach
                    @if ($paire->count() === 1)
                        <td class="label"></td><td class="valeur"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endforeach

    @if ($agent->observations)
        <table class="bloc">
            <tr><td class="bloc-titre">Observations</td></tr>
        </table>
        <div class="observations">{{ $agent->observations }}</div>
    @endif

    <div class="pied">
        Document généré le {{ now()->translatedFormat('d F Y à H:i') }} — GesPerES / MESFPTT
    </div>
</body>
</html>
