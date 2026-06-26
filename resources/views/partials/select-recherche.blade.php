{{-- Saisie intelligente : transforme tout <select data-recherche> en liste
     déroulante recherchable (autocomplétion). À inclure une fois par page. --}}
@once
    @push('head')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof TomSelect === 'undefined') return;
                document.querySelectorAll('select[data-recherche]').forEach(function (sel) {
                    if (sel.tomselect) return; // déjà initialisé
                    new TomSelect(sel, {
                        allowEmptyOption: true,
                        create: false,
                        sortField: { field: 'text', direction: 'asc' },
                    });
                });
            });
        </script>
    @endpush
@endonce
