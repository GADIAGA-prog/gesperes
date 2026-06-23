<?php

namespace App\Http\Controllers;

use App\Support\ManuelUsage;
use Illuminate\View\View;

/**
 * Manuel d'usage de la plateforme (page d'aide). Alimente aussi le chatbox.
 */
class ManuelController extends Controller
{
    public function index(): View
    {
        $rubriques = collect(ManuelUsage::rubriques())->groupBy('module');

        return view('manuel.index', ['rubriques' => $rubriques]);
    }
}
