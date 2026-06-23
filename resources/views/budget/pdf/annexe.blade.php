<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        .titre { text-align: center; font-size: 9px; margin: 0; }
        .titre.h { font-size: 11px; font-weight: bold; margin: 4px 0 6px; }
        .annexe-entete { font-weight: bold; font-size: 9px; margin: 6px 0 4px; }
        table.annexe { width: 100%; border-collapse: collapse; }
        table.annexe th, table.annexe td { border: 0.5px solid #999; padding: 2px 3px; }
        table.annexe thead { display: table-header-group; }
        table.annexe thead th { background: #dcfce7; text-align: left; }
        table.annexe td.r, table.annexe th.r { text-align: right; }
        table.annexe td.c { text-align: center; color: #777; }
        table.annexe tr.tot td { background: #fde2c4; font-weight: bold; }
        table.annexe tr { page-break-inside: avoid; }
        .page { page-break-after: always; }
    </style>
</head>
<body>
    @forelse ($detail as $pcode => $prog)
        @foreach ($prog['structures'] as $slib => $s)
            <div class="page">
                <p class="titre">MINISTÈRE DE L'ENSEIGNEMENT SECONDAIRE ET DE LA FORMATION PROFESSIONNELLE ET TECHNIQUE</p>
                <p class="titre">AVANT-PROJET DE BUDGET {{ $annees[0] }}-{{ $annees[2] }}</p>
                <p class="titre h">Tableau II-1 — Dépenses de personnel — Fonctionnaires et militaires présents en {{ $annees[0] }}</p>
                @include('budget._annexe_structure', ['pcode' => $pcode, 'plib' => $prog['libelle'], 'slib' => $slib, 'lignes' => $s['lignes'], 'totaux' => $s['totaux'], 'provisions' => $s['provisions'], 'annees' => $annees])
            </div>
        @endforeach
    @empty
        <p>Aucune donnée pour ce filtre.</p>
    @endforelse
</body>
</html>
