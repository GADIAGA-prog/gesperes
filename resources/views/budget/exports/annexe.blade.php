@foreach ($detail as $pcode => $prog)
    @foreach ($prog['structures'] as $slib => $s)
        @include('budget._annexe_structure', ['pcode' => $pcode, 'plib' => $prog['libelle'], 'slib' => $slib, 'lignes' => $s['lignes'], 'totaux' => $s['totaux'], 'provisions' => $s['provisions'], 'annees' => $annees])
        <table><tr><td></td></tr></table>
    @endforeach
@endforeach
