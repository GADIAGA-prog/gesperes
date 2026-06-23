<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Export Excel du tableau annexe « Dépenses de personnel » (détail par agent,
 * regroupé par programme → structure, avec Total et provisions).
 */
class AnnexePersonnelExport implements FromView
{
    public function __construct(
        private array $detail,
        private array $annees = [],
    ) {}

    public function view(): View
    {
        return view('budget.exports.annexe', [
            'detail' => $this->detail,
            'annees' => $this->annees,
        ]);
    }
}
